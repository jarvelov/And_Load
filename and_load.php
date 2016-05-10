<?php

error_reporting(-1);
ini_set('display_errors', 'On');

/*
Plugin Name: And Load
Plugin URI: http://tobias.jarvelov.se/portfolio/and_load
Description: Load style or script files using a shortcode.
Version: 1.0.2
Author: Tobias Järvelöv
Author Email: tobias@jarvelov.se
License: GPLv3
*/

// don't load directly
  if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
  }

  define( 'SLDIR', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) );
  define( 'SLURL', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) );

  class AndLoad {

    /*--------------------------------------------*
     * Constants
     *--------------------------------------------*/
    const name = 'And Load';
    const slug = 'and_load';

    /**
     * Constructor
     */
    function __construct() {
        //register an activation hook for the plugin
        register_activation_hook( __FILE__, array( $this, 'install_plugin' ) );

        //Hook up to the init action
        add_action( 'init', array( $this, 'init_plugin' ) );
    }

    /**
     * Runs when the plugin is activated
     */
    function install_plugin() {
        /* Create database table */
        global $wpdb;
        $table_name = $wpdb->prefix . 'and_load';

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT '' NOT NULL,
            slug varchar(255) DEFAULT '' NOT NULL,
            type varchar(255) DEFAULT '' NOT NULL,
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
    function init_plugin() {
        require 'vendor/autoload.php';

        // Register the shortcode: [and_load]
        if ( ! ( shortcode_exists( 'and_load' ) ) ) {
            add_shortcode( 'and_load', array( $this, 'render_shortcode' ) );
        }

        if ( is_admin() ) {
            if ( ! ( class_exists('And_Load_Options') ) ) {
                require(SLDIR . '/' . self::slug.'_options.php');
            }
            $this->options = new And_Load_Options();
        }

        if( ! ( class_exists('And_Load_Api') ) ) {
            require(SLDIR . '/' . self::slug . '_api.php');
            $this->api = new And_Load_Api();
        }
    }

    /** and_load_dump_shortcode_data
    * @data - (string) to be dumped to page
    * @wrap - (string) 'script' | 'style'- Wrap @data within these tags
    */
    function and_load_dump_shortcode_data($data, $wrap) {
        switch ($wrap) {
            case 'script':
                $content = '<script type="text/javascript">' . $data . '</script>';
                break;

            case 'style':
                $content = '<style type="text/css">' . $data . '</style';
                break;

            case 'script_url':
                $content = '(function() {
                    var d = document, s = d.createElement("script");
                    s.src = "//' . $data . '";
                    s.setAttribute("data-timestamp", + new Date());
                    (d.head || d.body).appendChild(s);
                })()';
                break;

            case 'style_url':
                $content = '(function() {
                    var d = document, s = d.createElement("style");
                    s.rel = "stylesheet";
                    s.href = "//' . $data . '";
                    s.setAttribute("data-timestamp", + new Date());
                    (d.head || d.body).appendChild(s);
                })()';
                break;

            default:
                break;
        }

        if( isset($content) ) {
            echo $content;
        }
    } //end and_load_dump_shortcode_data

    /** and_load_shortcode_file_enqueue_operation
    *
    */
    function and_load_shortcode_file_enqueue_operation($result, $shortcode_args) {
        extract( $result ); //turn array into named variables, see $sql SELECT query for variable names
        extract( $shortcode_args ); //turn array into named variables, see render_shortcode function

        //Get default options
        $options_default = get_option( 'and_load_default_options' );
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
                $file_path = $this->and_load_get_file_path($path, $revision_override, $type, $minify);
            } else {
                if($revision_override >= 0) {
                    $file_path = $this->and_load_get_file_path($path, false, $type, $minify);
                } else {
                    $file_path = $this->and_load_get_file_path($path, $revision, $type, $minify);
                }
            }
        } else {
            if($revision > 0) {
                $file_path = $this->and_load_get_file_path($path, $revision, $type, $minify);
            } else {
                $file_path = $this->and_load_get_file_path($path, false, $type, $minify);
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

        $this->and_load_enqueue_file( $name, $file_path, $is_script, $dependencies);

    } // end and_load_shortcode_file_enqueue_operation

    /** render_shortcode
    *
    */
    function render_shortcode($atts) {
        // Extract the attributes submitted with the shortcode
        $args = (shortcode_atts(array(
            'id' => false,
            'revision_override' => false,
            'minify_override' => false,
            'jquery_override' => false,
            'data' => false,
            'data_wrap' => false
            ), $atts));

        if( $args['id'] ) {
            //Enqueue files
        }

        //Dump data to page if argument was given
        if($args['data']) {
            $this->and_load_dump_shortcode_data( $args['data'], $args['data_wrap'] );
        }
    }

    public function ajax_script($hook) {
        if( 'index.php' != $hook ) {
        // Only applies to dashboard panel
        return;
        }
            
        wp_enqueue_script( 'and-load-ajax-script', plugins_url( '/js/ajax.js', __FILE__ ), array('jquery') );
    }

    /** and_load_enqueue_file
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name            The ID to register with WordPress
     * @file_path       The path to the actual file, can be an URL
     * @is_script       Optional argument for if the incoming file_path is a JavaScript source file.
     * @dependencies    Optional argument to specifiy file dependencies such as jQuery, underscore etc.
     */
    public function and_load_enqueue_file( $name, $file_path, $is_script = false, $dependencies = false) {
        $local_file_path = plugin_dir_path(__FILE__) . $file_path;

        if( file_exists( $local_file_path ) ) {
            $file_url = plugins_url($file_path, __FILE__);
            $this->and_load_register_and_enqueue($name, $file_url, $is_script, $dependencies);
        } elseif( file_exists( $file_path ) ) { //variable is not a path within the plugin directory but may be somewhere else on the server, such as the wp-uploads directory
            $file_url = get_site_url() . '/' . substr($file_path, strlen(ABSPATH));
            $this->and_load_register_and_enqueue($name, $file_url, $is_script, $dependencies);
        } elseif(! (filter_var($file_path, FILTER_VALIDATE_URL) === false) ) { //$file_path is an URL
            $this->and_load_register_and_enqueue($name, $file_path, $is_script, $dependencies);
        } // end if
    } // end and_load_enqueue_file

    function and_load_register_and_enqueue($name, $path, $is_script = false, $dependencies = false)  {
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

    function and_load_get_file_path($path, $revision, $type, $minify) {
        $suffix = ( $minify ) ? 'min.' . $type : $type;
        $srcname = basename($path, $suffix);

        $path_src_base = dirname($path) . '/';

        if($revision) {
            $path = $path_src_base . $srcname . $revision . "." . $suffix;
        } else {
            $path = $path_src_base . $srcname . $suffix;
        }

        return $path;
    } // end and_load_get_file_path

} // end class
new AndLoad();

?>
