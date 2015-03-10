<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

$option_names = array('shortcode_load_new_script_options','shortcode_load_default_options','shortcode_load_new_style_options','shortcode_load_edit_file_options','shortcode_load_default_options');

foreach ($option_names as $option_name) {
	delete_option( $option_name );
}

//drop shortcode_load db table
global $wpdb;
$table_name = $wpdb->prefix . 'shortcode_load'; 
$wpdb->query( "DROP TABLE IF EXISTS " . $table_name );