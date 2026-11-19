<?php
/* Generic list / add / edit / delete screen driven by a config array. */
function crud_cell(array $f, $v): string
{
    $type = $f['type'] ?? 'text';
    if ($type === 'image') {
        return $v ? '<img class="thumb" src="' . e(img_url($v)) . '" alt="">' : '—';
    }
    if ($type === 'select') {
        return e($f['options'][$v] ?? $v);
    }
    $s = (string)$v;
    if ($s === '') {
        return '—';
    }
    if (mb_strlen($s) > 70) {
        $s = mb_substr($s, 0, 70) . '…';
    }
    return e($s);
}

function crud_run(array $cfg): void
{
    $pdo    = db();
    $table  = $cfg['table'];        // fixed in code, never from user input
    $fields = $cfg['fields'];
    $self   = $cfg['self'];
    $one    = $cfg['singular'];
    $hasImg = false;
    foreach ($fields as $f) {
        if (($f['type'] ?? '') === 'image') {
            $hasImg = true;
        }
    }

    $errors = [];
    $values = [];
    $action = $_GET['action'] ?? 'list';
    $id     = (int)($_GET['id'] ?? 0);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash_set('error', 'Your security token was invalid or expired. Please try again.');
            redirect('admin/' . $self);
        }
        $do = $_POST['do'] ?? '';

        if ($do === 'delete') {
            $st = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
            $st->execute([(int)($_POST['id'] ?? 0)]);
            $row = $st->fetch();
            if ($row) {
                foreach ($fields as $k => $f) {
                    if (($f['type'] ?? '') === 'image') {
                        delete_upload($row[$k] ?? null);
                    }
                }
                $pdo->prepare("DELETE FROM `$table` WHERE id = ?")->execute([$row['id']]);
                flash_set('success', $one . ' deleted.');
            } else {
                flash_set('error', 'Record not found.');
            }
            redirect('admin/' . $self);
        }

        if ($do === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $existing = null;
            if ($id) {
                $st = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
                $st->execute([$id]);
                $existing = $st->fetch();
                if (!$existing) {
                    flash_set('error', 'Record not found.');
                    redirect('admin/' . $self);
                }
            }

            $vals = fields_validate($fields, $_POST, $errors);
            $imgs = ['values' => [], 'new' => [], 'delete' => []];
            if (!$errors) {
                $imgs = fields_images($fields, $existing, $_POST, $_FILES, $errors);
                if ($errors) {
                    foreach ($imgs['new'] as $n) {
                        delete_upload($n);
                    }
                }
            }

            if (!$errors) {
                $data = $vals + $imgs['values'];
                if ($id) {
                    $set    = implode(', ', array_map(function ($k) { return "`$k` = ?"; }, array_keys($data)));
                    $params = array_values($data);
                    $params[] = $id;
                    $pdo->prepare("UPDATE `$table` SET $set WHERE id = ?")->execute($params);
                } else {
                    $cols = implode(', ', array_map(function ($k) { return "`$k`"; }, array_keys($data)));
                    $qs   = implode(', ', array_fill(0, count($data), '?'));
                    $pdo->prepare("INSERT INTO `$table` ($cols) VALUES ($qs)")->execute(array_values($data));
                }
                foreach ($imgs['delete'] as $old) {
                    delete_upload($old);
                }
                flash_set('success', $one . ($id ? ' updated.' : ' added.'));
                redirect('admin/' . $self);
            }

            // Validation failed: redisplay the form
            $values = array_merge($existing ?? [], $_POST);
            if ($existing) {
                foreach ($fields as $k => $f) {
                    if (($f['type'] ?? '') === 'image') {
                        $values[$k] = $existing[$k];
                    }
                }
            }
            $action = $id ? 'edit' : 'add';
        }
    }

    if ($action === 'edit' && !$values) {
        $st = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
        $st->execute([$id]);
        $values = $st->fetch();
        if (!$values) {
            flash_set('error', 'Record not found.');
            redirect('admin/' . $self);
        }
    }

    admin_header($cfg['title'], $cfg['active']);

    if ($action === 'add' || $action === 'edit') {
        $formUrl = url('admin/' . $self . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=add'));
        ?>
        <div class="panel">
          <h2><?= $action === 'edit' ? 'Edit ' : 'Add ' ?><?= e(strtolower($one)) ?></h2>
          <?php if ($errors): ?><div class="notice notice-error">Please fix the highlighted fields.</div><?php endif; ?>
          <form method="post" action="<?= e($formUrl) ?>"<?= $hasImg ? ' enctype="multipart/form-data"' : '' ?> novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="save">
            <input type="hidden" name="id" value="<?= (int)($action === 'edit' ? $id : 0) ?>">
            <?php fields_render($fields, $values, $errors); ?>
            <div class="form-actions">
              <button class="btn" type="submit">Save</button>
              <a class="btn btn-ghost" href="<?= e(url('admin/' . $self)) ?>">Cancel</a>
            </div>
          </form>
        </div>
        <?php
    } else {
        $rows = $pdo->query("SELECT * FROM `$table` ORDER BY " . $cfg['order'])->fetchAll();
        ?>
        <div class="panel">
          <div class="panel-head">
            <h2><?= e($cfg['title']) ?> <span class="muted">(<?= count($rows) ?>)</span></h2>
            <a class="btn" href="<?= e(url('admin/' . $self . '?action=add')) ?>">+ Add <?= e(strtolower($one)) ?></a>
          </div>
          <?php if (!$rows): ?>
            <p class="muted">Nothing here yet.</p>
          <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead><tr>
                <?php foreach ($cfg['list'] as $k): ?><th><?= e($fields[$k]['label'] ?? $k) ?></th><?php endforeach; ?>
                <th class="actions">Actions</th>
              </tr></thead>
              <tbody>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <?php foreach ($cfg['list'] as $k): ?><td><?= crud_cell($fields[$k], $r[$k]) ?></td><?php endforeach; ?>
                  <td class="actions">
                    <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/' . $self . '?action=edit&id=' . (int)$r['id'])) ?>">Edit</a>
                    <form method="post" action="<?= e(url('admin/' . $self)) ?>" data-confirm="Delete this <?= e(strtolower($one)) ?>? This cannot be undone." class="inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="do" value="delete">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
        <?php
    }
    admin_footer();
}
