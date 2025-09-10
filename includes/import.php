<?php

function vi_import_vacancies($count_limit = 100) {
    $stats = [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
    ];

    $imported_count = 0;
    $page = 1;

    do {
        $endpoint = add_query_arg('page', $page, 'https://www.arbeitnow.com/api/job-board-api');
        $response = wp_safe_remote_get($endpoint, ['timeout' => 20]);

        if (is_wp_error($response)) {
            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($data['data'])) break;

        foreach ($data['data'] as $item) {
            $source_id = !empty($item['slug'])
                ? sanitize_text_field($item['slug'])
                : sanitize_text_field($item['url']);

            $existing_posts = get_posts([
                'post_type' => 'vacancy',
                'meta_key' => 'source_id',
                'meta_value' => $source_id,
                'posts_per_page' => 1,
            ]);

            $post_data = [
                'post_type'    => 'vacancy',
                'post_title'   => $item['title'] ?? '',
                'post_content' => $item['description'] ?? '',
                'post_status'  => 'publish',
            ];

            $is_updated = false;

            if ($existing_posts) {
                $post_data['ID'] = $existing_posts[0]->ID;
                $post_id = wp_update_post($post_data);

                if ($post_id) {
                    $is_updated = true;
                    $stats['updated']++;
                }
            } else {
                $post_id = wp_insert_post($post_data);
                if ($post_id) {
                    $stats['created']++;
                }
            }

            if ($post_id) {
                update_post_meta($post_id, 'company', $item['company_name'] ?? '');
                update_post_meta($post_id, 'location', $item['location'] ?? '');
                update_post_meta($post_id, 'salary_min', $item['salary_min'] ?? null);
                update_post_meta($post_id, 'salary_max', $item['salary_max'] ?? null);
                update_post_meta($post_id, 'currency', $item['currency'] ?? null);
                update_post_meta($post_id, 'source_id', $source_id);
                update_post_meta($post_id, 'remote', !empty($item['remote']));
                update_post_meta($post_id, 'source_url', $item['url'] ?? '');
                update_post_meta(
                    $post_id,
                    'published_at',
                    !empty($item['created_at']) ? gmdate('Y-m-d H:i:s', strtotime($item['created_at'])) : ''
                );

                if (!empty($item['tags']) && is_array($item['tags'])) {
                    wp_set_object_terms($post_id, $item['tags'], 'vacancy_tag');
                }

                if (!empty($item['job_types']) && is_array($item['job_types'])) {
                    wp_set_object_terms($post_id, $item['job_types'], 'vacancy_job_type');
                }

                if (!$is_updated) {
                    $imported_count++;
                }
            } else {
                $stats['skipped']++;
            }

            if ($imported_count >= $count_limit) break 2;
        }

        $page++;
    } while (!empty($data['links']['next']));

    return $stats;
}

function vi_reset_vacancies() {
    $vacancies = get_posts([
        'post_type' => 'vacancy',
        'numberposts' => -1,
        'post_status' => 'any',
        'fields' => 'ids',
    ]);

    foreach ($vacancies as $vid) {
        wp_delete_post($vid, true);
    }

    $taxonomies = ['vacancy_tag', 'vacancy_job_type'];
    foreach ($taxonomies as $tax) {
        $terms = get_terms(['taxonomy' => $tax, 'hide_empty' => false]);
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $tax);
        }
    }
}


if (defined('WP_CLI') && WP_CLI) {

    WP_CLI::add_command('vacancies import', function($args, $assoc_args) {
        $count_limit = !empty($assoc_args['count']) ? intval($assoc_args['count']) : 100;
        $stats = vi_import_vacancies($count_limit);

        if (is_wp_error($stats)) {
            WP_CLI::error($stats->get_error_message());
        } else {
            WP_CLI::success("Vacancies import completed.");
            WP_CLI::line("Created: {$stats['created']}");
            WP_CLI::line("Updated: {$stats['updated']}");
            WP_CLI::line("Skipped: {$stats['skipped']}");
            WP_CLI::line("Total processed: " . ($stats['created'] + $stats['updated']));
        }
    });

    WP_CLI::add_command('vacancies clear', function() {
        vi_reset_vacancies();
        WP_CLI::success("All vacancies and related taxonomies deleted.");
    });
}
