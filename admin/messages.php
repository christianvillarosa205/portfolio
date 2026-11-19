<?php
require __DIR__ . '/includes/init.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash_set('error', 'Your security token was invalid or expired. Please try again.');
        redirect('admin/messages.php');
    }
    $do = $_POST['do'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $back = (($_POST['back'] ?? '') === 'view') ? 'admin/messages.php?view=' . $id : 'admin/messages.php';

    if ($do === 'read' || $do === 'unread') {
        $pdo->prepare('UPDATE messages SET is_read = ? WHERE id = ?')->execute([$do === 'read' ? 1 : 0, $id]);
        flash_set('success', 'Message marked as ' . $do . '.');
    } elseif ($do === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        flash_set('success', 'Message deleted.');
        $back = 'admin/messages.php';
    } elseif ($do === 'read_all') {
        $pdo->exec('UPDATE messages SET is_read = 1');
        flash_set('success', 'All messages marked as read.');
    }
    redirect($back);
}

$viewId = (int)($_GET['view'] ?? 0);
if ($viewId) {
    $st = $pdo->prepare('SELECT * FROM messages WHERE id = ?');
    $st->execute([$viewId]);
    $msg = $st->fetch();
    if (!$msg) {
        flash_set('error', 'Message not found.');
        redirect('admin/messages.php');
    }
    if (!$msg['is_read']) {
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$viewId]);
        $msg['is_read'] = 1;
    }
    admin_header('Message', 'messages');
    ?>
    <div class="panel">
      <div class="panel-head">
        <h2><?= e($msg['subject']) ?></h2>
        <a href="<?= e(url('admin/messages.php')) ?>">← Back to inbox</a>
      </div>
      <dl class="profile">
        <dt>From</dt><dd><?= e($msg['name']) ?> &lt;<a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a>&gt;</dd>
        <dt>Received</dt><dd><?= e(date('F j, Y g:i A', strtotime($msg['created_at']))) ?></dd>
        <dt>IP address</dt><dd><?= e($msg['ip_address']) ?></dd>
      </dl>
      <div class="message-body"><?= nl2br(e($msg['message'])) ?></div>
      <div class="form-actions">
        <a class="btn" href="mailto:<?= e($msg['email']) ?>?subject=<?= e(rawurlencode('Re: ' . $msg['subject'])) ?>">Reply by email</a>
        <form method="post" action="<?= e(url('admin/messages.php')) ?>" class="inline">
          <?= csrf_field() ?><input type="hidden" name="do" value="unread"><input type="hidden" name="id" value="<?= (int)$msg['id'] ?>"><input type="hidden" name="back" value="view">
          <button class="btn btn-ghost" type="submit">Mark as unread</button>
        </form>
        <form method="post" action="<?= e(url('admin/messages.php')) ?>" class="inline" data-confirm="Delete this message permanently?">
          <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
          <button class="btn btn-danger" type="submit">Delete</button>
        </form>
      </div>
    </div>
    <?php
    admin_footer();
    exit;
}

// Inbox list
$filter  = ($_GET['filter'] ?? '') === 'unread' ? 'unread' : 'all';
$where   = $filter === 'unread' ? 'WHERE is_read = 0' : '';
$perPage = 15;
$total   = (int)$pdo->query("SELECT COUNT(*) FROM messages $where")->fetchColumn();
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$offset  = ($page - 1) * $perPage;
$rows    = $pdo->query("SELECT * FROM messages $where ORDER BY created_at DESC, id DESC LIMIT $perPage OFFSET $offset")->fetchAll();

admin_header('Messages', 'messages');
?>
<div class="panel">
  <div class="panel-head">
    <div class="tabs">
      <a class="<?= $filter === 'all' ? 'active' : '' ?>" href="<?= e(url('admin/messages.php')) ?>">All</a>
      <a class="<?= $filter === 'unread' ? 'active' : '' ?>" href="<?= e(url('admin/messages.php?filter=unread')) ?>">Unread (<?= (int)unread_count() ?>)</a>
    </div>
    <?php if (unread_count() > 0): ?>
      <form method="post" action="<?= e(url('admin/messages.php')) ?>" class="inline">
        <?= csrf_field() ?><input type="hidden" name="do" value="read_all">
        <button class="btn btn-sm btn-ghost" type="submit">Mark all as read</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (!$rows): ?>
    <p class="muted">No messages to show.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th></th><th>From</th><th>Subject</th><th>Received</th><th class="actions">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $m): ?>
        <tr class="<?= $m['is_read'] ? '' : 'unread' ?>">
          <td><?= $m['is_read'] ? '' : '<span class="dot" title="Unread"></span>' ?></td>
          <td><?= e($m['name']) ?><br><small class="muted"><?= e($m['email']) ?></small></td>
          <td><a href="<?= e(url('admin/messages.php?view=' . (int)$m['id'])) ?>"><?= e($m['subject']) ?></a></td>
          <td><?= e(date('M j, Y g:i A', strtotime($m['created_at']))) ?></td>
          <td class="actions">
            <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/messages.php?view=' . (int)$m['id'])) ?>">View</a>
            <form method="post" action="<?= e(url('admin/messages.php')) ?>" class="inline">
              <?= csrf_field() ?><input type="hidden" name="do" value="<?= $m['is_read'] ? 'unread' : 'read' ?>"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="btn btn-sm btn-ghost" type="submit"><?= $m['is_read'] ? 'Mark unread' : 'Mark read' ?></button>
            </form>
            <form method="post" action="<?= e(url('admin/messages.php')) ?>" class="inline" data-confirm="Delete this message permanently?">
              <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="btn btn-sm btn-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= e(url('admin/messages.php?filter=' . $filter . '&page=' . $i)) ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
