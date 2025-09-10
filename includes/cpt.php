<?php
function vacancy_register_cpt() {
    $labels = [
        'name' => 'Vacancies',
        'singular_name' => 'Vacancy',
        'add_new_item' => 'Add Vacancy',
        'edit_item' => 'Edit Vacancy'
    ];
    register_post_type('vacancy', [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);
}

function vi_register_taxonomies() {
    register_taxonomy('vacancy_tag', 'vacancy', [
        'label' => 'Tags',
        'hierarchical' => false,
        'show_in_rest' => true,
    ]);
    register_taxonomy('vacancy_job_type', 'vacancy', [
        'label' => 'Job Types',
        'hierarchical' => false,
        'show_in_rest' => true,
    ]);
}

add_action('init', 'vacancy_register_cpt');
add_action('init', 'vi_register_taxonomies');
