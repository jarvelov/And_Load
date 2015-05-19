<?php

/*
Plugin Name: Shortcode Load
Plugin URI: http://jarvelov.se/portfolio/shortcode_load
Description: Load style or script files using a shortcode.
Version: 0.9a
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
            slug varchar(255) DEFAULT '' NOT NULL,
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

        // Register the shortcode: [shortcode_load]
        add_shortcode( 'shortcode_load', array( &$this, 'render_shortcode' ) );
        
        if ( is_admin() ) {
            if ( ! ( class_exists("ShortcodeLoad_Options") ) ) {
                require(SLDIR . '/' . self::slug.'_options.php');
            }
            $this->options = new ShortcodeLoad_Options();

        }

        add_action( 'register_scripts_and_styles', array( &$this, 'action_callback_register_scripts_and_styles' ) );

        do_action( 'register_scripts_and_styles' );

    }

    function action_callback_register_scripts_and_styles() {
        $this->register_scripts_and_styles();
    }

    function render_shortcode($atts) {
        // Extract the attributes submitted with the shortcode
        extract(shortcode_atts(array(
            'id' => '',
            'revision_override' => false,
            'minify_override' => false,
            'jquery_override' => false,
            'in_header' => false,
            'args' => ''
            ), $atts));

        //TODO handle in_header arg
        if($id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'shortcode_load'; 

            $sql = "SELECT name,srcpath,minify,minpath,type,revision FROM ".$table_name." WHERE id = '" . intval( $id ) . "' LIMIT 1";
            $result = $wpdb->get_results($sql, ARRAY_A)[0];

            if(sizeof($result) > 0 )  {
                extract($result);

                //Get default options
                $options_default = get_option( 'shortcode_load_default_options' );
                $default_minify = $options_default['default_minify'];
                $default_jquery = $options_default['default_jquery'];

                $minify_override = ( $minify_override == 'true' ) ? true : false;
                $jquery_override = ( $jquery_override == 'true' ) ? true : false;

                if(!$minify_override AND $default_minify) {
                    $minify = true;
                    $path = $minpath;
                } else {
                    $minify = false;
                    $path = $srcpath;
                }

                if($revision_override !== false) {
                    if($revision_override <= $revision AND $revision_override > 0) {
                        $path_external = $this->shortcode_load_get_path_external($path, $revision_override, $type, $minify);
                    } else {
                        if($revision_override >= 0) {
                            $path_external = $this->shortcode_load_get_path_external($path, false, $type, $minify);
                        } else {
                            $path_external = $this->shortcode_load_get_path_external($path, $revision, $type, $minify);
                        }
                    }
                } else {
                    if($revision > 0) {
                        $path_external = $this->shortcode_load_get_path_external($path, $revision, $type, $minify);
                    } else {
                        $path_external = $this->shortcode_load_get_path_external($path, false, $type, $minify);
                    }
                }

                $is_script = ($type == 'js') ? true : false;
                $dependencies = false;

                if($is_script) {
                    if($in_header) {
                        $dependencies = false; //
                    } elseif($default_jquery AND !$jquery_override) {
                        $dependencies = array('jquery');
                    } elseif($default_jquery AND $jquery_override) {
                        $dependencies = false;
                    } elseif(!$default_jquery AND $jquery_override) {
                        $dependencies = array('jquery');
                    }
                }

                $this->load_file( $name, $path_external, $is_script, $dependencies);
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
        } else {
            $this->load_file( self::slug . '-script', self::slug . '-script/js/widget.js', true );
            $this->load_file( self::slug . '-style', self::slug . '-style/css/widget.css' );
        } // end if/else
    } // end register_scripts_and_styles
    
    /**
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name            The ID to register with WordPress
     * @file_path       The path to the actual file, can be an URL
     * @is_script       Optional argument for if the incoming file_path is a JavaScript source file.
     */
    public function load_file( $name, $file_path, $is_script = false, $dependencies = false, $in_footer = false ) {

        $url = plugins_url($file_path, __FILE__);
        $file = plugin_dir_path(__FILE__) . $file_path;

        var_dump($file_path);

        if( file_exists( $file ) ) {
            if( $is_script ) {
                wp_register_script( $name, $url, $dependencies, false, $in_footer );
                wp_enqueue_script( $name, $url, $dependencies, false, $in_footer );
            } else {
                wp_register_style( $name, $url );
                wp_enqueue_style( $name );
            } // end if
        } else { //variable is not a local file path, possibly hosted remotely or an URL to the local server was given for a file not located within the plugin directory
            if( ! (filter_var($file_path, FILTER_VALIDATE_URL) === false) ) { //validate url before registering
                if( $is_script ) {
                    wp_register_script( $name, $file_path, $dependencies, false, $in_footer );
                    wp_enqueue_script( $name, $file_path, $dependencies, false, $in_footer ); 
                } else {
                    wp_register_style( $name, $file_path );
                    wp_enqueue_style( $name );
                }
            }
        }

    } // end load_file


    function shortcode_load_get_path_external($path, $revision, $type, $minify) {
        $suffix = ( $minify ) ? 'min.' . $type : $type;
        $srcname = basename($path, $suffix);

        $path_src_base = dirname($path) . '/';

        if($revision) {
            $path = $path_src_base . $srcname . $revision . "." . $suffix;
        } else {
            $path = $path_src_base . $srcname . $suffix;
        }
        
        $site_url = get_site_url();

        $path_external = str_replace(ABSPATH, $site_url . '/', $path);

        return $path_external;
    }

} // end class
new ShortcodeLoad();

?>