<?php
/*
Plugin Name: Vacancy Importer
Description: Import vacancies from Arbeitnow API.
Version: 1.0.0
Author: Max Shchyrba
*/

if (!defined('ABSPATH')) exit;

define('VI_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once VI_PLUGIN_DIR . 'includes/cpt.php';
require_once VI_PLUGIN_DIR . 'includes/acf-fields.php';
require_once VI_PLUGIN_DIR . 'includes/import.php';
require_once VI_PLUGIN_DIR . 'includes/rest.php';
require_once VI_PLUGIN_DIR . 'includes/block.php';

register_activation_hook(__FILE__, function() {
    vacancy_register_cpt();
    vi_register_taxonomies();
    flush_rewrite_rules();
});
