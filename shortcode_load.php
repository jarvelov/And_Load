<?php
/*
Plugin Name: Shortcode Load
Plugin URI: http://jarvelov.se/portfolio/shortcode_load
Description: Load style or script files using a shortcode.
Version: 0.1
Author: Tobias Järvelöv
Author Email: tobias@jarvelov.se
License:

  Copyright 2011 Tobias Järvelöv (tobias@jarvelov.se)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

// don't load directly
  if (!function_exists('is_admin')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
  }

  define( 'SLDIR', WP_PLUGIN_DIR . '/shortcode_load' );
  define( 'SLURL', WP_PLUGIN_URL . '/shortcode_load' );

  class ShortcodeLoad {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'Shortcode Load';
	const slug = 'shortcode_load';
	
	/**
	 * Constructor
	 */
	function __construct() {
		//register an activation hook for the plugin
		register_activation_hook( __FILE__, array( &$this, 'install_shortcode_load' ) );

		//Hook up to the init action
		add_action( 'init', array( &$this, 'init_shortcode_load' ) );
	}
	
	/**
	 * Runs when the plugin is activated
	 */  
	function install_shortcode_load() {
		/* Create database table */
		global $wpdb;
		$table_name = $wpdb->prefix . 'shortcode_load';

		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) DEFAULT '' NOT NULL,
			type varchar(255) DEFAULT '' NOT NULL,
			srcpath varchar(255) DEFAULT '' NOT NULL,
			minify boolean DEFAULT '0' NOT NULL,
			minpath varchar(255) DEFAULT '',
			revision mediumint(9) DEFAULT '1' NOT NULL,
			created_timestamp timestamp DEFAULT '0000-00-00 00:00:00',
			updated_timestamp timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			UNIQUE KEY id (id)
			) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Runs when the plugin is initialized
	 */
	function init_shortcode_load() {
		// Setup localization
		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		// Load JavaScript and stylesheets

		// Register the shortcode [shortcode_load]
		add_shortcode( 'shortcode_load', array( &$this, 'render_shortcode' ) );
		
		if ( is_admin() ) {
			if (!class_exists("ShortcodeLoad_Options"))
				require(SLDIR . '/' . self::slug.'_options.php');
			$this->options = new ShortcodeLoad_Options();

		} else {
			//this will run when on the frontend
		}

		add_action( 'register_scripts_and_styles', array( &$this, 'action_callback_register_scripts_and_styles' ) );

		do_action( 'register_scripts_and_styles' );

	}

	function action_callback_register_scripts_and_styles() {
		$this->register_scripts_and_styles();
	}

	function render_shortcode($atts) {
		// Extract the attributes
		extract(shortcode_atts(array(
			'id' => '',
			'in_header' => false
			), $atts));

		if($id) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'shortcode_load'; 

			$sql = "SELECT name,srcpath,minpath,minify,type,revision FROM ".$table_name." WHERE id = '".$id."' LIMIT 1";
			$result = $wpdb->get_results($sql, ARRAY_A)[0];
		}

		if(sizeof($result) > 0 )  {
			extract($result);

			$is_script = ($type == 'js') ? true : false;

			if($minify == 1) {
				$path = $minpath;
				$suffix = 'min.' . $type;
			} else {
				$path = $srcpath;
				$suffix = $type;
			}

			$site_url = get_site_url();

			if($revision > 0) {
				$srcname = basename($path, $suffix);
				$path_src_base = dirname($path) . '/';
				$path = $path_src_base . $srcname . $revision . "." . $suffix;
			}

			$path_external = str_replace(ABSPATH, $site_url . '/', $path);

			var_dump($path_external);

			//TODO make this cleaner and wrap enqueue in a function

			if($is_script) {
				wp_register_script( $name, $path_external ); //depends on jquery
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $path_external );
				wp_enqueue_style( $name );
			}
		}
	}
	
	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {
		if ( is_admin() ) {
			$this->load_file( self::slug . '-admin-script', self::slug . '-admin-script/js/admin.js', true );
			$this->load_file( self::slug . '-admin-style', self::slug . '-admin-style/css/admin.css' );
			$this->load_file( self::slug . '-ace-js', 'lib/ace/src-min-noconflict/ace.js', true );
			$this->load_file( self::slug . '-ace-css', self::slug . '-admin-style/css/ace.css' );
		} else {
			$this->load_file( self::slug . '-script', self::slug . '-script/js/widget.js', true );
			$this->load_file( self::slug . '-style', self::slug . '-style/css/widget.css' );
		} // end if/else
	} // end register_scripts_and_styles
	
	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name			The ID to register with WordPress
	 * @file_path		The path to the actual file, can be an URL
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	public function load_file( $name, $file_path, $is_script = false ) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_register_script( $name, $url, array('jquery') ); //depends on jquery
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} else { //variable is not of a local file, possibly hosted remotely
			if( ! (filter_var($file_path, FILTER_VALIDATE_URL) === false) ) { //validate url before registering
				if( $is_script ) {
					wp_register_script( $name, $file_path, array('jquery') ); //depends on jquery
					wp_enqueue_script( $name );	
				} else {
					wp_register_style( $name, $file_path );
					wp_enqueue_style( $name );
				}
			}
		}

	} // end load_file
	
} // end class
new ShortcodeLoad();

?>