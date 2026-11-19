<?php
require __DIR__ . '/includes/init.php';
crud_run([
    'table' => 'projects', 'title' => 'Projects', 'singular' => 'Project', 'self' => 'projects.php', 'active' => 'projects',
    'order' => 'sort_order, id DESC',
    'list'  => ['image', 'title', 'category', 'sort_order'],
    'fields' => [
        'title'       => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 120],
        'category'    => ['label' => 'Category', 'type' => 'select', 'required' => true,
                          'options' => ['web' => 'Web', 'design' => 'Design', 'academic' => 'Academic', 'other' => 'Other']],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'max' => 1000, 'rows' => 4],
        'image'       => ['label' => 'Screenshot / image', 'type' => 'image'],
        'project_url' => ['label' => 'Project link (optional)', 'type' => 'url', 'max' => 255, 'help' => 'Must start with http:// or https://'],
        'sort_order'  => ['label' => 'Display order', 'type' => 'number', 'default' => 0, 'help' => 'Lower numbers appear first.'],
    ],
]);
