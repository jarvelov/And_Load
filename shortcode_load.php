<?php

/*
Plugin Name: Shortcode Load
Plugin URI: http://jarvelov.se/portfolio/shortcode_load
Description: Load style or script files using a shortcode.
Version: 0.9.1a
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
        if ( ! ( shortcode_exists( 'shortcode_load' ) ) ) {
            add_shortcode( 'shortcode_load', array( &$this, 'shortcode_load_render_shortcode' ) );
        }
        
        if ( is_admin() ) {
            if ( ! ( class_exists("ShortcodeLoad_Options") ) ) {
                require(SLDIR . '/' . self::slug.'_options.php');
            }
            $this->options = new ShortcodeLoad_Options();

            //Enqueue styles for administration panel
            $this->shortcode_load_enqueue_styles();
        }
    }
    /** shortcode_load_dump_shortcode_data
    * @data - (string) to be dumped to page
    * @wrap - (string) 'script' | 'style'- Wrap @data within these tags
    */
    function shortcode_load_dump_shortcode_data($data, $wrap) {
        switch ($wrap) {
            case 'script':
                $content = '<script type="text/javascript">' . $data . '</script>';
                break;
            
            case 'style':
                $content = '<style type="text/css">' . $data . '</style';
                break;
            default:
                break;
        }

        if( isset($content) ) {
            echo $content;
        }
    } //end shortcode_load_dump_shortcode_data

    /** shortcode_load_shortcode_file_enqueue_operation
    *
    */
    function shortcode_load_shortcode_file_enqueue_operation($result, $shortcode_args) {
        extract( $result ); //turn array into named variables, see $sql SELECT query for variable names
        extract( $shortcode_args ); //turn array into named variables, see render_shortcode function 

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
                $file_path = $this->shortcode_load_get_file_path($path, $revision_override, $type, $minify);
            } else {
                if($revision_override >= 0) {
                    $file_path = $this->shortcode_load_get_file_path($path, false, $type, $minify);
                } else {
                    $file_path = $this->shortcode_load_get_file_path($path, $revision, $type, $minify);
                }
            }
        } else {
            if($revision > 0) {
                $file_path = $this->shortcode_load_get_file_path($path, $revision, $type, $minify);
            } else {
                $file_path = $this->shortcode_load_get_file_path($path, false, $type, $minify);
            }
        }

        $is_script = ($type == 'js') ? true : false;
        $dependencies = false;

        if($is_script) {
            if($default_jquery AND !$jquery_override) {
                $dependencies = array('jquery');
            } elseif($default_jquery AND $jquery_override) {
                $dependencies = false;
            } elseif(!$default_jquery AND $jquery_override) {
                $dependencies = array('jquery');
            }
        }

        $this->shortcode_load_enqueue_file( $name, $file_path, $is_script, $dependencies);

    } // end shortcode_load_shortcode_file_enqueue_operation

    /** shortcode_load_render_shortcode
    *
    */
    function shortcode_load_render_shortcode($atts) {
        // Extract the attributes submitted with the shortcode
        $args = (shortcode_atts(array(
            'id' => false,
            'revision_override' => false,
            'minify_override' => false,
            'jquery_override' => false,
            'data' => false,
            'data_wrap' => 'raw'
            ), $atts));

        if( $id ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'shortcode_load'; 

            $sql_limit = 10;
            $sql = 'SELECT name,srcpath,minify,minpath,type,revision FROM ' . $table_name . ' WHERE  ';

            $ids = explode(",", $id);
            for ($i=0; $i < sizeof($ids) OR $i < $sql_limit; $i++) { 
                $current_id = $ids[$i];
                if($i == 0) {
                    $sql .= 'id = ' . intval( $current_id );
                } else {
                    $sql .= ' OR id = ' . intval( $current_id );
                }
            }

            $sql .= ' LIMIT ' . $sql_limit;

            try {
                $result = $wpdb->get_results($sql, ARRAY_A);    
            } catch(Exception $e) {
                var_dump($e);
            }

            var_dump($result);

            //TODO: Something prevents the most current revision to be loaded with JS files
            if( isset($result) ) {
                for ($i=0; $i < sizeof( $result ); $i++) { 
                    $current_file = $result[$i];
                    $this->shortcode_load_shortcode_file_enqueue_operation($current_file, $args);
                }

                //Dump data to page if argument was given
                if($data) {
                    $this->shortcode_load_dump_shortcode_data( $args['data'], $args['data_wrap'] );
                }
            }
        }
    }
    
    /** shortcode_load_enqueue_styles
     * Registers and enqueues stylesheets for the administration panel
     */
    private function shortcode_load_enqueue_styles() {
        if ( is_admin() ) {
            $this->shortcode_load_enqueue_file( self::slug . '-bootstrap-css', 'lib/bootstrap/css/bootstrap.min.css' );
            $this->shortcode_load_enqueue_file( self::slug . '-admin-style', 'css/admin.css' );
        } // end if
    } // end shortcode_load_enqueue_styles
    
    /** shortcode_load_enqueue_file
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name            The ID to register with WordPress
     * @file_path       The path to the actual file, can be an URL
     * @is_script       Optional argument for if the incoming file_path is a JavaScript source file.
     * @dependencies    Optional argument to specifiy file dependencies such as jQuery, underscore etc.
     */
    public function shortcode_load_enqueue_file( $name, $file_path, $is_script = false, $dependencies = false) {
        $local_file_path = plugin_dir_path(__FILE__) . $file_path;

        if( file_exists( $local_file_path ) ) {
            $file_url = plugins_url($file_path, __FILE__);
            $this->shortcode_load_register_and_enqueue($name, $file_url, $is_script, $dependencies);
        } elseif( file_exists( $file_path ) ) { //variable is not a path within the plugin directory but may be somewhere else on the server, such as the wp-uploads directory
            $file_url = get_site_url() . '/' . substr($file_path, strlen(ABSPATH));
            $this->shortcode_load_register_and_enqueue($name, $file_url, $is_script, $dependencies);
        } elseif(! (filter_var($file_path, FILTER_VALIDATE_URL) === false) ) { //$file_path is an URL
            $this->shortcode_load_register_and_enqueue($name, $file_path, $is_script, $dependencies);
        } // end if
    } // end shortcode_load_enqueue_file

    function shortcode_load_register_and_enqueue($name, $path, $is_script = false, $dependencies = false)  {
        try {
            if( $is_script ) {
                wp_register_script( $name, $path, $dependencies );
                wp_enqueue_script( $name );
            } else {
                wp_register_style( $name, $path );
                wp_enqueue_style( $name );
            } // end if
        } catch(Exception $e) {
            //var_dump($e);
        }
    }

    function shortcode_load_get_file_path($path, $revision, $type, $minify) {
        $suffix = ( $minify ) ? 'min.' . $type : $type;
        $srcname = basename($path, $suffix);

        $path_src_base = dirname($path) . '/';

        if($revision) {
            $path = $path_src_base . $srcname . $revision . "." . $suffix;
        } else {
            $path = $path_src_base . $srcname . $suffix;
        }

        return $path;
    } // end shortcode_load_get_file_path

} // end class
new ShortcodeLoad();

?>