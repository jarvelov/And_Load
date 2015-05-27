<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

function deleteOptions() {
	$option_names = array(
		'and_load_default_options',
		'and_load_edit_file_options',
	);

	foreach ($option_names as $option_name) {
		delete_option( $option_name );
	}	
}

function dropTable() {
	//drop and_load db table
	global $wpdb;
	$table_name = $wpdb->prefix . 'and_load'; 
	$wpdb->query( "DROP TABLE IF EXISTS " . $table_name );
}

if ( !is_multisite() ) {
	deleteOptions();
	dropTable();
} else {
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();

    foreach ( $blog_ids as $blog_id ) 
    {
        switch_to_blog( $blog_id );
		deleteOptions();
		dropTable();
    }

    restore_current_blog();
}

