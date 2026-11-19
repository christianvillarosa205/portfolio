<?php
require __DIR__ . '/includes/init.php';
crud_run([
    'table' => 'skills', 'title' => 'Skills', 'singular' => 'Skill', 'self' => 'skills.php', 'active' => 'skills',
    'order' => 'category, sort_order, id',
    'list'  => ['name', 'category', 'sort_order'],
    'fields' => [
        'name'       => ['label' => 'Skill name', 'type' => 'text', 'required' => true, 'max' => 80],
        'category'   => ['label' => 'Category', 'type' => 'select', 'required' => true,
                         'options' => ['Technical' => 'Technical', 'Personal' => 'Personal', 'Tools' => 'Tools', 'Languages' => 'Languages', 'Other' => 'Other']],
        'sort_order' => ['label' => 'Display order', 'type' => 'number', 'default' => 0, 'help' => 'Lower numbers appear first.'],
    ],
]);
