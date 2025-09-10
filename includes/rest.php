<?php
add_action('rest_api_init', function() {
    register_rest_route('vacancies/v1', '/import', [
        'methods'=>'POST',
        'callback'=>'vi_rest_import',
        'permission_callback'=>function(){ return current_user_can('manage_options'); },
    ]);
});

function vi_rest_import($request){
    check_admin_referer('vi_import_nonce', '_wpnonce');
    vi_import_vacancies();
    return ['success'=>true];
}
