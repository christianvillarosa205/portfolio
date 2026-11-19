<?php
require __DIR__ . '/includes/init.php';
crud_run([
    'table' => 'experience', 'title' => 'Experience', 'singular' => 'Experience', 'self' => 'experience.php', 'active' => 'experience',
    'order' => 'sort_order, id DESC',
    'list'  => ['position', 'organization', 'period', 'sort_order'],
    'fields' => [
        'position'     => ['label' => 'Position / role', 'type' => 'text', 'required' => true, 'max' => 120],
        'organization' => ['label' => 'Organization', 'type' => 'text', 'max' => 150],
        'period'       => ['label' => 'Period', 'type' => 'text', 'max' => 50, 'help' => 'e.g. Jun 2025 – Present'],
        'description'  => ['label' => 'Description', 'type' => 'textarea', 'max' => 1500, 'rows' => 4],
        'sort_order'   => ['label' => 'Display order', 'type' => 'number', 'default' => 0, 'help' => 'Lower numbers appear first.'],
    ],
]);
