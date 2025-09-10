<?php
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group([
    'key' => 'group_vacancy_fields',
    'title' => 'Vacancy Fields',
    'fields' => [
        ['key'=>'field_company','label'=>'Company','name'=>'company','type'=>'text'],
        ['key'=>'field_location','label'=>'Location','name'=>'location','type'=>'text'],
        ['key'=>'field_salary_min','label'=>'Salary Min','name'=>'salary_min','type'=>'number'],
        ['key'=>'field_salary_max','label'=>'Salary Max','name'=>'salary_max','type'=>'number'],
        ['key'=>'field_currency','label'=>'Currency','name'=>'currency','type'=>'text'],
        ['key'=>'field_source_id','label'=>'Source ID','name'=>'source_id','type'=>'text'],
        ['key'=>'field_remote','label'=>'Remote','name'=>'remote','type'=>'true_false'],
        ['key'=>'field_source_url','label'=>'Source URL','name'=>'source_url','type'=>'url'],
        ['key'=>'field_published_at','label'=>'Published At','name'=>'published_at','type'=>'date_time_picker'],
    ],
    'location' => [[['param'=>'post_type','operator'=>'==','value'=>'vacancy']]],
]);

endif;
