<?php

add_action('init', function() {

    register_block_type('vi/vacancies-list', [
        'editor_script'   => 'vi-vacancies-block-editor', 
        'render_callback' => 'vi_render_vacancies_block',
        'attributes'      => [
            'perPage'       => ['type'=>'number','default'=>10],
            'sort'          => ['type'=>'string','default'=>'published_at'],
            'location'      => ['type'=>'string','default'=>''],
            'salaryMin'     => ['type'=>'number','default'=>0],
            'salaryMax'     => ['type'=>'number','default'=>0],
            'paged'         => ['type'=>'number','default'=>1],
            'cf7_shortcode' => ['type'=>'string','default'=>''], 
        ],
    ]);
});

add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'vi-vacancies-block-editor',
        plugins_url('../assets/block-editor.js', __FILE__),
        ['wp-blocks','wp-element','wp-block-editor','wp-components','wp-server-side-render'],
        false,
        true
    );
});

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'vi-vacancies-block-frontend',
        plugins_url('../assets/block-frontend.js', __FILE__),
        [],
        false,
        true
    );

    wp_enqueue_style(
        'vi-vacancies-frontend-css',
        plugins_url('../assets/block-frontend.css', __FILE__),
        [],
        false
    );

    wp_localize_script('vi-vacancies-block-frontend', 'viVacancies', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
});


add_action('wp_ajax_vi_get_vacancies', 'vi_ajax_get_vacancies');
add_action('wp_ajax_nopriv_vi_get_vacancies', 'vi_ajax_get_vacancies');

function vi_ajax_get_vacancies() {
    $attrs = [
        'perPage'       => intval($_POST['perPage'] ?? 10),
        'sort'          => sanitize_text_field($_POST['sort'] ?? 'published_at'),
        'location'      => sanitize_text_field($_POST['location'] ?? ''),
        'salaryMin'     => intval($_POST['salaryMin'] ?? 0),
        'salaryMax'     => intval($_POST['salaryMax'] ?? 0),
        'paged'         => intval($_POST['paged'] ?? 1),
        'cf7_shortcode' => sanitize_text_field($_POST['cf7_shortcode'] ?? ''), 
    ];

    echo vi_render_vacancies_block($attrs);
    wp_die();
}


function vi_render_vacancies_block($attrs) {
    $paged = max(1,intval($attrs['paged'] ?? 1));
    $perPage = intval($attrs['perPage'] ?? 10);
    $location = sanitize_text_field($attrs['location'] ?? '');
    $sort = $attrs['sort'] ?? 'published_at';
    $salaryMin = intval($attrs['salaryMin'] ?? 0);
    $salaryMax = intval($attrs['salaryMax'] ?? 0);
    $cf7_shortcode = $attrs['cf7_shortcode'] ?? ''; 

    $args = ['post_type'=>'vacancy','posts_per_page'=>$perPage,'paged'=>$paged,'order'=>'DESC'];

    if($sort==='title') $args['orderby']='title';
    elseif($sort==='salary') {$args['meta_key']='salary_min'; $args['orderby']='meta_value_num';}
    else {$args['meta_key']='published_at'; $args['orderby']='meta_value';}

    $meta_query=[];
    if($location) $meta_query[]=['key'=>'location','value'=>$location,'compare'=>'LIKE'];
    if($salaryMin) $meta_query[]=['key'=>'salary_min','value'=>$salaryMin,'compare'=>'>='];
    if($salaryMax) $meta_query[]=['key'=>'salary_max','value'=>$salaryMax,'compare'=>'<='];
    if(!empty($meta_query)) $args['meta_query']=$meta_query;

    $q = new WP_Query($args);
    ob_start();

  echo '<div class="vi-vacancies-wrapper" data-attrs="'.esc_attr(json_encode([
    'perPage' => $perPage,
    'sort' => $sort,
    'location' => $location,
    'salaryMin' => $salaryMin,
    'salaryMax' => $salaryMax,
    'paged' => $paged,
    'cf7_shortcode' => '' 
])).'">';



    echo '<div class="vi-vacancies-filters">';

    echo '<div class="vi-filter-item">';
    echo '<label for="vi-filter-location">Location</label>';
    echo '<input type="text" id="vi-filter-location" class="vi-filter-location" placeholder="Location" value="'.esc_attr($location).'">';
    echo '</div>';

    echo '<div class="vi-filter-item vi-filter-salary-range">';
    echo '<label for="vi-filter-salary-max">Max Salary: <span class="salary-max-display">'.esc_html($salaryMax).'</span></label>';
    echo '<input type="range" id="vi-filter-salary-max" class="vi-filter-salary-max" min="0" max="100000" value="'.esc_attr($salaryMax).'" 
    aria-valuemin="0" aria-valuemax="100000" aria-valuenow="'.esc_attr($salaryMax).'" aria-valuetext="'.esc_attr($salaryMax).'">';
    echo '</div>';

    echo '<div class="vi-filter-item">';
    echo '<label for="vi-filter-sort">Sort by</label>';
    echo '<select id="vi-filter-sort" class="vi-filter-sort">
            <option value="published_at"'.($sort==='published_at'?' selected':'').'>Date</option>
            <option value="salary"'.($sort==='salary'?' selected':'').'>Salary</option>
            <option value="title"'.($sort==='title'?' selected':'').'>Title</option>
        </select>';
    echo '</div>';

    echo '<div class="vi-filter-item">';
    echo '<label for="vi-filter-perpage">Per page</label>';
    echo '<input type="number" id="vi-filter-perpage" class="vi-filter-perpage" placeholder="Per page" value="'.esc_attr($perPage).'">';
    echo '</div>';

    echo '<button class="vi-filter-apply">Apply</button>';
    echo '</div>';

    echo '<div class="vi-vacancies-list">';
    if($q->have_posts()){
        while($q->have_posts()){ $q->the_post();
            $company = get_post_meta(get_the_ID(),'company',true);
            $locationVal = get_post_meta(get_the_ID(),'location',true);
            $salary_min = get_post_meta(get_the_ID(),'salary_min',true);
            $salary_max = get_post_meta(get_the_ID(),'salary_max',true);
            $source_url = get_post_meta(get_the_ID(),'source_url',true);

            echo '<div class="vi-vacancy-card">';
            echo '<h3>'.get_the_title().'</h3>';
            echo '<p>'.esc_html($company).' | '.esc_html($locationVal).'</p>';
            echo '<p>Salary: '.($salary_min??'N/A').' - '.($salary_max??'N/A').'</p>';
            echo '<button class="vi-apply-btn" data-vacancy-id="'.get_the_ID().'" data-vacancy-title="'.esc_attr(get_the_title()).'" aria-label="Apply for '.esc_attr(get_the_title()).'">Apply</button>';
            echo '</div>';
        }
    } else { echo '<p class="vacancies-empty">No vacancies found.</p>'; }
    echo '</div>';

    $total_pages = $q->max_num_pages;
    if($total_pages > 1){
        echo '<div class="vi-vacancies-pagination">';
        $range = 2;
        for($i = 1; $i <= $total_pages; $i++){
            if($i == 1 || $i == $total_pages || ($i >= $paged - $range && $i <= $paged + $range)){
                $active = ($i==$paged)?'active':'';
                echo '<button class="vi-vac-page '.$active.'" data-page="'.$i.'" aria-label="Go to page '.$i.'">'.$i.'</button>';
            } elseif($i == 2 && $paged - $range > 2){
                echo '<span class="dots">…</span>';
            } elseif($i == $total_pages - 1 && $paged + $range < $total_pages - 1){
                echo '<span class="dots">…</span>';
            }
        }
        echo '</div>';
    }

    wp_reset_postdata();
    echo '</div>'; 

    if($cf7_shortcode){
    echo '<div id="vi-cf7-modal-root" class="vi-cf7-modal-root" style="display:none;">';
    echo '<div class="vi-cf7-modal-overlay" data-action="close"></div>';
    echo '<div class="vi-cf7-modal-box" role="dialog" aria-modal="true" aria-labelledby="vi-cf7-modal-title">';
    echo '<h2 id="vi-cf7-modal-title" class="screen-reader-text">Application Form</h2>';
    echo '<button class="vi-cf7-modal-close" aria-label="Close">&times;</button>';
    echo do_shortcode($cf7_shortcode);
    echo '</div>';
    echo '</div>';  
    }

    return ob_get_clean();
}
