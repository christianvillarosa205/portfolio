<?php
require __DIR__ . '/includes/init.php';
crud_run([
    'table' => 'achievements', 'title' => 'Achievements', 'singular' => 'Achievement', 'self' => 'achievements.php', 'active' => 'achievements',
    'order' => 'sort_order, achieved_on DESC, id DESC',
    'list'  => ['image', 'title', 'issuer', 'achieved_on'],
    'fields' => [
        'title'       => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 150, 'help' => 'Certificate, award or achievement name'],
        'issuer'      => ['label' => 'Issued by', 'type' => 'text', 'max' => 150],
        'achieved_on' => ['label' => 'Date', 'type' => 'date'],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'max' => 1000, 'rows' => 3],
        'image'       => ['label' => 'Certificate image', 'type' => 'image'],
        'sort_order'  => ['label' => 'Display order', 'type' => 'number', 'default' => 0, 'help' => 'Lower numbers appear first.'],
    ],
]);
