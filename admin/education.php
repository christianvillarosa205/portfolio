<?php
require __DIR__ . '/includes/init.php';
crud_run([
    'table' => 'education', 'title' => 'Education', 'singular' => 'Education record', 'self' => 'education.php', 'active' => 'education',
    'order' => 'sort_order, id',
    'list'  => ['school', 'level', 'period', 'sort_order'],
    'fields' => [
        'school'     => ['label' => 'School', 'type' => 'text', 'required' => true, 'max' => 150],
        'level'      => ['label' => 'Level', 'type' => 'text', 'max' => 80, 'help' => 'e.g. Senior high school'],
        'period'     => ['label' => 'Period', 'type' => 'text', 'max' => 50, 'help' => 'e.g. 2022–2024'],
        'details'    => ['label' => 'Details (optional)', 'type' => 'textarea', 'max' => 1000, 'rows' => 3],
        'sort_order' => ['label' => 'Display order', 'type' => 'number', 'default' => 0, 'help' => 'Lower numbers appear first.'],
    ],
]);
