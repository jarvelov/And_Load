<?php

error_reporting(-1);
ini_set('display_errors', 'On');

Class AndLoad_Options extends AndLoad {

    function __construct() {
        add_action( 'admin_menu', array($this, 'and_load_add_admin_menu') );
        add_action( 'admin_init', array($this, 'and_load_settings_init') );
        $this->templates = new League\Plates\Engine( dirname(__FILE__) . '/templates');
    } //end __construct

    function and_load_add_admin_menu(  ) { 
        add_menu_page( 'And_Load', 'And_Load', 'manage_options', 'and_load', array($this, 'and_load_options_page') );
    } // end and_load_add_admin_menu

    function and_load_settings_init()  {

        if ( ! current_user_can('update_plugins') )
            return;

        /* Overview tab section */

        add_settings_section( 
            'and_load_overview',
            '',
            array($this, 'and_load_overview_callback'),
            'and_load_overview'
        );    

        register_setting('and_load_overview', 'and_load_overview');

        /* Default settings tab section */

        add_settings_section( 
            'and_load_default',
            'Default Settings',
            array($this, 'and_load_default_options_callback'),
            'and_load_default_options'
        );

        add_settings_field(
            'and_load_general',
            'General',
            array($this, 'and_load_default_general_settings_callback'),
            'and_load_default_options',
            'and_load_default',
            $this->and_load_get_options_default_general()
        );

        add_settings_field(
            'and_load_overview',
            'Overview',
            array($this, 'and_load_default_overview_callback'),
            'and_load_default_options',
            'and_load_default',
            $this->and_load_get_options_default_overview()
        );

        add_settings_field(
            'and_load_default_editor_settings',
            'Editor settings',
            array($this, 'and_load_default_editor_settings_callback'),
            'and_load_default_options',
            'and_load_default',
            $this->and_load_get_options_default_editor()
        );

        register_setting('and_load_default_options', 'and_load_default_options', array($this, 'and_load_default_options_callback_sanitize'));

        /* Edit file tab section */

        add_settings_section( 
            'and_load_edit_file',
            '',
            array($this, 'and_load_edit_file_options_callback'),
            'and_load_edit_file_options'
        );

        register_setting('and_load_edit_file_options', 'and_load_edit_file_options', array($this, 'and_load_edit_file_callback_sanitize'));

        /* Help tab section */

        register_setting('and_load_help_section', 'and_load_help_section');

        //Register default options
        $this->and_load_register_default_options();
    } // end and_load_settings_init

    /***************************
    * Default option arguments *
    ***************************/

    /** and_load_get_options_default_general
    * Returns an array with the default general options
    */

    function and_load_get_options_default_general() {
        $options = array(
            'default_minify' => true, //set default to auto minify for all file types
            'default_jquery' => true //set default to add jquery as dependency for script files                
        );

        return $options;
    } //end and_load_get_options_default_general

    /** and_load_get_options_default_overview
    * Returns an array with the default options for overview tab
    */

    function and_load_get_options_default_overview() {
        $options = array(
            'overview_default_table_order_column' => 0,
            'overview_default_table_order_columns' => array(
                'id' => 0,
                'Type' => 1,
                'Name' => 2,
                'Revisions' => 3,
                'Last Updated' => 4,
                'Created' => 5
            ),
            'overview_default_table_sort' => 'desc',
            'overview_default_table_sort_types' => array(
                'Ascending' => 'asc',
                'Descending' => 'desc'
            )
        );

        return $options;
    } // end and_load_get_options_default_overview

    /** and_load_get_options_default_editor
    * Returns an array with the default options for the tab_edit editor
    */
    function and_load_get_options_default_editor() {
        $options = array(
            'editor_themes' => array(
                'Ambiance' => 'ambiance',
                'Chaos' => 'chaos',
                'Chrome' => 'chrome',
                'Clouds' => 'clouds',
                'Clouds Midnight' => 'clouds_midnight',
                'Cobalt' => 'cobalt',
                'Crimson Editor' => 'crimson_editor',
                'Dawn' => 'dawn',
                'Dreamweaver' => 'dreamweaver',
                'Eclipse' => 'eclipse',
                'GitHub' => 'github',
                'Idle Fingers' => 'idle_fingers',
                'Katzenmilch' => 'katzenmilch',
                'Kr Theme' => 'kr_theme',
                'Kuroir' => 'kuroir',
                'Merbivore' => 'merbivore',
                'Merbivore Soft' => 'merbivore_soft',
                'Monokai (default)' => 'monokai',
                'Mono Industrial' => 'mono_industrial',
                'Pastel on Dark' => 'pastel_on_dark',
                'Solarized Dark' => 'solarized_dark',
                'Solarized Light' => 'solarized_light',
                'Terminal' => 'terminal',
                'Textmate' => 'textmate',
                'Tomorrow' => 'tomorrow',
                'Tomorrow Night' => 'tomorrow_night',
                'Tomorrow Night Blue' => 'tomorrow_night_blue',
                'Tomorrow Night Bright' => 'tomorrow_night_bright',
                'Tomorrow Night Eighties' => 'tomorrow_night_eighties',
                'Twilight' => 'twilight',
                'Vibrant Ink' => 'vibrant_ink',
                'Xcode' => 'xcode'
            ),
            'editor_font_sizes' => array(8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 20, 22, 24),
            'editor_mode_types' => array('JavaScript' => 'javascript', 'CSS' => 'css', 'None' => 'plain_text'),
            'editor_default_theme' => 'monokai',
            'editor_default_mode_type' => 'plain_text',
            'editor_default_tab_size_override' => false,
            'editor_default_print_margin' => true,
            'editor_default_show_line_numbers' => true,
            'editor_default_font_size' => 12,
            'editor_default_tab_size' => 4,
            'editor_default_print_margin_column' => 80                
        );

        return $options;
    }

    /** and_load_save_to_database
    * Save a new script or style to the database
    *
    * @args - named array
    *
    * $args = array(
            'content' => (string),
            'name' => (string),
            'type' => 'js' | 'css',
            'minify' => (bool)
        )
    */
    function and_load_save_to_database($args) {
        try {
            $db_args = $this->and_load_save_file( $args );
        } catch (Exception $e) {
            $error_id = isset( $error_id ) ? $error_id : $e->getCode();
        }

        if( ( ! isset( $error_id ) ) ) {
            try {
                global $wpdb;
                $table_name = $wpdb->prefix . 'and_load'; 

                $wpdb->insert( 
                    $table_name, 
                    array( 
                        'name' => $db_args['name'],
                        'slug' => $db_args['slug'],
                        'type' => $db_args['type'],
                        'srcpath' =>  $db_args['srcpath'],
                        'minify' => $db_args['minify'],
                        'minpath' => $db_args['minpath'],
                        'revision' => 0,
                        'created_timestamp' => current_time('mysql', 1),
                        'updated_timestamp' => current_time('mysql', 1),
                    ), 
                    array( 
                        '%s', //name
                        '%s', //slug
                        '%s', //type
                        '%s', //srcpath
                        '%d', //minify
                        '%s', //minpath
                        '%d', //revision
                        '%s', //created_timestamp
                        '%s' //updated_timestamp
                    ) 
                );

                $id = $wpdb->insert_id;
            } catch (Exception $e) {
                //var_dump($e);
                $error_id = isset( $error_id ) ? $error_id : 1;; //error writing to database
            }

            $name = $db_args['name'] . '.' . $db_args['type'];
            $type = ($db_args['type'] == 'js') ? 'Script' : 'Style';
            $return_args = array('success' => true, 'id' => $id, 'name' => $name, 'type' => $type, 'operation' => 'saved');
        } else {
            $return_args = array('success' => false, 'error_id' => $error_id, 'operation' => 'saved');
        }

        return $return_args;
    } // end and_load_save_to_database

    /* and_load_update_database_record
    * Updates a record in the database
    *
    * @id - (int)
    * @revision - (int)
    * @minify - (bool)
    */
    function and_load_update_database_record($id, $revision, $minify) {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'and_load';
            $result = $wpdb->update( 
                $table_name, 
                array( 
                    'revision' => $revision,    // int
                    'updated_timestamp' => current_time('mysql', 1),
                    'minify' => $minify
                ), 
                array( 
                    'id' => $id
                ), 
                array(
                    '%d', //revision
                    '%s', //updated_timestamp
                    '%d' //minify
                ),
                array('%d') //id
            );

            if($result > 0) {
                $return_args = array('success' => true);
            } else {
                $return_args = array('success' => false);
            }
        } catch (Exception $e) {
            //var_dump($e);
            throw new Exception("Could not update database record", 4);
        }
        return $return_args;
    } // end and_load_update_database_record

    function and_load_delete_database_record($id) {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'and_load';

            $wpdb->delete( $table_name,
                array( 'id' => intval($id) ),
                array( '%d' )
            );
        } catch (Exception $e) {
            $error_id = isset( $error_id ) ? $error_id : 19;
            throw new Exception("Error deleting file with id: $id", $error_id);
        }
    } // end and_load_delete_database_record

    /* and_load_save_file
    * Save a new script or style to a file in wordpress' uploads folder
    * @args - named array
    * $args = array(
            'content' => (string),
            'name' => (string),
            'type' => 'js' | 'css',
            'minify' => (bool)
        )
    */
    function and_load_save_file($args) {
        $wp_uploads_path = wp_upload_dir();
        $uploads_dir = $wp_uploads_path['basedir'] . '/and_load/';

        $minify = $args['minify'];
        $content = $args['content'];
        $org_name = $args['name'];

        if( ! $org_name) {
            throw new Exception("Error invalid file name", 21);
        }

        switch ( strtolower( $args['type'] ) ) {
            case 'javascript':
                $type = 'js';
                break;

            case 'js':
                $type = 'js';
                break;

            case 'css':
                $type = 'css';
                break;

            default:
                throw new Exception("Error invalid file type", 20);
                break;
        }

        $src_dir = $uploads_dir . $type . '/src/';
        $min_dir = $uploads_dir . $type . '/min/';
        
        $random5 = substr( md5( microtime() ), rand( 0,26 ), 5); //generate 5 random characters to ensure filename is unique
        $slug = $org_name . '.' . $random5;

        $slug = $this->and_load_filter_string( $slug ); //filter out any characters we don't want in path

        $file_src = $src_dir . $slug . '.' . $type;

        try {
            if ( ! ( is_dir( $src_dir ) ) ) {
                wp_mkdir_p( $src_dir );
            }
        } catch(Exception $e) {
            $error_id = isset( $error_id ) ? $error_id : 16;
            throw new Exception("Error creating directory for files.", $error_id);
        }

        try {
            if($minify == true) {
                if ( ! ( is_dir( $min_dir ) ) ) {
                    wp_mkdir_p( $min_dir );
                }
            }
        } catch(Exception $e) {
            $error_id = isset( $error_id ) ? $error_id : 17;
            throw new Exception("Error creating directory for minified files.", $error_id);
        }

        try {
            $file_args = $this->and_load_save_file_to_path( $file_src, $content, $type, $minify );
        } catch(Exception $e) {
            //var_dump($e);
            $error_id = isset( $error_id ) ? $error_id : $e->getCode();
        }

        if( isset( $file_args) ) {
            return array('name' => $org_name, 'slug' => $slug, 'type' => $type, 'srcpath' => $file_args['srcpath'], 'minify' => $minify, 'minpath' => $file_args['minpath']);
        } else {
            if( ! ( isset( $error_id ) ) ) {
                $error_id = 13; //General error
            }

            throw new Exception("Error saving file.", $error_id);
        }
    } // end and_load_save_file

    /* and_load_save_file_to_path
    * Save file content to path,
    * optionally save a minified version
    *
    * @path - (string) local path to write @content to
    * @content - (string)
    * @type - (string) 'js' | 'css'
    * @minify - (bool)
    */
    function and_load_save_file_to_path($path, $content, $type, $minify) {
        $file_args_array = array();

        try {
            file_put_contents($path, $content);
            $file_args_array['srcpath'] = $path;
            $file_args_array['success'] = true;
        } catch (Exception $e) {
            throw new Exception("Error saving file to path", 12);
        }

        $slug = basename($path, '.' . $type);
        $path_min = dirname(dirname($path)) . '/min/' . $slug . '.min.' . $type;
        $file_args_array['minpath'] = $path_min;

        if($minify == true) {
            try {
                $minified_content = $this->and_load_minify_file( $content, $type );
            } catch (Exception $e) {
                //var_dump($e);
                $error_id = isset( $error_id ) ? $error_id : $e->getCode();
                throw new Exception("Error minifying file.", $error_id);
            }

            try {
                file_put_contents($path_min, $minified_content);
            } catch(Exception $e) {
                //var_dump($e);
                $error_id = isset( $error_id ) ? $error_id : 12;
                throw new Exception("Error saving minified file content to path.", $error_id);
            }

            $file_args_array['success'] = true;
        }

        return $file_args_array;
    } // end and_load_save_file_to_path

    /** and_load_add_file_revision
    * Add a new revision of a file
    *
    * @id- (int)
    * @content- (string)
    * @minify - (bool)
    **/
    function and_load_add_file_revision($id, $content, $minify) {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'and_load'; 

            $sql = "SELECT name,slug,type,revision,srcpath,minify FROM ".$table_name." WHERE id = ".intval($id)." LIMIT 1";
            $result = $wpdb->get_results($sql, ARRAY_A)[0];
        } catch (Exception $e) {
            //var_dump($e);
            $error_id = isset( $error_id ) ? $error_id : 2; //2 = database lookup error
        }

        if( isset( $result ) ) {
            extract($result); //extract array to named variables, see $sql SELECT query above for variable names
            $options_default = get_option( 'and_load_default_options' );
            $minify = ( $options_default['default_minify'] ) ? $options_default['default_minify'] : $minify; //TODO reverse this logic when adding an option to disable minify per file

            $new_revision = ( intval($revision) + 1);

            $srcname = basename($srcpath, $type);
            $unique_suffix = str_replace($slug, "", $srcname);
            $new_slug = $slug . $unique_suffix . $new_revision . "." . $type;

            $file_src_base = dirname($srcpath) . '/';
            $file_src = $file_src_base . $new_slug;

            try {
                $file_args = $this->and_load_save_file_to_path( $file_src, $content, $type, $minify );
            } catch(Exception $e) {
                $error_id = isset( $error_id ) ? $error_id : $e->getCode();
            }
        }

        if( isset( $file_args ) ) {
            if($file_args['success'] == true) {
                try {
                    $result = $this->and_load_update_database_record( intval($id), $new_revision, $minify);
                    $type = ($type == 'js') ? 'Script' : 'Style';
                    $return_args = array('success' => true, 'id' => $id, 'name' => $name, 'type' => $type, 'operation' => 'updated');
                } catch(Exception $e) {
                    $error_id = isset( $error_id ) ? $error_id : $e->getCode();
                    $return_args = array('success' => false, 'error_id' => $error_id);
                }
            }
        } else {
            $return_args = array('success' => false, 'error_id' => $error_id);
        }

        return $return_args;
    } // end and_load_add_file_revision

    /** and_load_delete_file
    * Delete a file and all revisions of it
    *
    * @id - ID of file to delete
    *
    **/

    function and_load_delete_file($id) {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'and_load'; 

            $sql = "SELECT name,type,revision,srcpath,minify,minpath FROM ".$table_name." WHERE id = ".intval($id)." LIMIT 1";
            $result = $wpdb->get_results($sql, ARRAY_A)[0];
        } catch (Exception $e) {
            //var_dump($e);
            $error_id = isset( $error_id ) ? $error_id : 2; //2 = database lookup error
        }

        if( isset( $result ) ) {
            extract( $result ); //extract array to named variables, see $sql SELECT query above for variable names
            $minify = ( $options_default['default_minify'] ) ? $options_default['default_minify'] : $minify; //TODO reverse this logic when adding an option to disable minify per file

            if( $minify ) {
                for ($i=0; $i <= $revision; $i++) {
                    $file_name = basename($srcpath, $type);
                    $file_path_base = dirname($minpath) . '/';
                    $file = ($i == 0)  ? ( $file_path_base . $file_name . "min." . $type ) :  ( $file_path_base . $file_name . $i . ".min." . $type );

                    try {
                        $this->and_load_delete_file_from_path( $file );
                    } catch(Exception $e) {
                        $error_id = isset( $error_id ) ? $error_id : $e->getCode();
                    }
                }
            }

            for ($i=0; $i <= $revision; $i++) {
                $file_name = basename($srcpath, $type);
                $file_path_base = dirname($srcpath) . '/';
                $file = ($i == 0)  ? ( $file_path_base . $file_name . $type ) :  ( $file_path_base . $file_name . $i . "." . $type );

                try {
                    $this->and_load_delete_file_from_path( $file );
                } catch(Exception $e) {
                    $error_id = isset( $error_id ) ? $error_id : $e->getCode();
                }
            }
        }

        try {
            $result = $this->and_load_delete_database_record( $id );
            $type = ($type == 'js') ? 'Script' : 'Style';
            $return_args = array('success' => true, 'id' => $id, 'name' => $name, 'type' => $type, 'operation' => 'deleted');
        } catch(Exception $e) {
            $error_id = isset( $error_id ) ? $error_id : $e->getCode();
        }

        if(isset( $error_id )) {
            $return_args = array('success' => false, 'error_id' => $error_id);
        }

        return $return_args;
    } // end and_load_delete_file

    /*
    * Minify javascript and css code
    */
    function and_load_minify_file($content, $type) {
        if ( class_exists("AndLoad") ) {
            if ( ! class_exists('AndLoad_Minify') ) {
                try {
                    require(dirname(__FILE__) . '/' . AndLoad::slug.'_minify.php');
                } catch(Exception $e) {
                    //var_dump($e);
                    throw new Exception("AndLoad minify class could not be loaded", 14);
                }
            }
        }

        try {
            $AndLoad_Minify = new AndLoad_Minify();
            $minified_content = $AndLoad_Minify->and_load_minify_minify_file($content, $type);
        } catch (Exception $e) {
            //var_dump($e);
            $error_id = isset( $error_id ) ? $error_id : $e->getCode();
            throw new Exception("Error Processing Request", $error_id);
        }

        return $minified_content;
    } // end and_load_minify_file

    /*
    * Return all saved file entries in database
    */
    function and_load_get_scripts_styles() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'and_load'; 

        $sql = "SELECT id,name,slug,type,revision,updated_timestamp,created_timestamp FROM ".$table_name." ORDER BY created_timestamp DESC";
        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    } // end and_load_get_scripts_styles

    /*
    * Returns a file's content.
    * If the file isn't found bool(false) is returned.
    * @path - Path to file
    */

    function and_load_get_file($path) {
        if( file_exists( $path ) ) {
            try {
                $content = file_get_contents($path);
            } catch (Exception $e) {
                $content = "Error reading file. Verify file integrity and permissions for file with local path: ".$path;
            }
        } else {
            $content = false;
        }
        return $content;
    } // end and_load_get_file

    function and_load_delete_file_from_path($path) {
        if( file_exists( $path ) ) {
            if( ! ( unlink( $path ) ) ) {
                throw new Exception("Error deleting file with path: $path", 18);
            }
        }
    } // end and_load_delete_file_from_path

    /************
    * Callbacks *
    ************/

    function and_load_register_default_options() {
        if( ! get_option('and_load_default_options') ) {
            $options_default = array();

            //Get general default options
            $general_options = $this->and_load_get_options_default_general();

            //Add general options to $options_default
            foreach ($general_options as $key => $value) {
               $options_default[$key] = $value;
            }

            //Get overview default options
            $overview_options = $this->and_load_get_options_default_overview();

            //Add overview options to $options_default
            foreach ($overview_options as $key => $value) {
               $options_default[$key] = $value;
            }

            //Get editor default options
            $editor_options = $this->and_load_get_options_default_editor();

            //Add editor options to $options_default
            foreach ($editor_options as $key => $value) {
               $options_default[$key] = $value;
            }

            //Register default options
            update_option( 'and_load_default_options', $options_default );
        }
    }

    /* Overview tab callbacks */

    function and_load_overview_callback() {
        echo $this->templates->render('tab_overview', [
            'header' => [
                'id', 'type', 'name', 'revisions', 'updated', 'created'
            ],
            'files' => $this->and_load_get_scripts_styles()
        ]);
    }
    /* Default options tab callbacks */

    function and_load_default_options_callback() {
        $html = '<p>Default options to configure overview and editor settings.</p>'; 
        $html .= '<p id="container_help_text"><span id="help-title">Tip!</span><span id="help_text">Hover mouse cursor over each setting title to get more info about it.</span></p>';

        echo $html;
    }

    function and_load_default_general_settings_callback($args) {
        $options_default = get_option( 'and_load_default_options' );

        $html = '<div id="default_general_container">';

        //Auto save/load minified files
        $minify_checkbox_value = isset ( $options_default['default_minify'] ) ? $options_default['default_minify'] : $args['default_minify'];
        $html .= '<div id="default_minify_setting_container" class="default_options_sub_setting">';
        $html .= '<label class="control-label" title="Automatically save a minified copy when saving/updating a file. If unchecked all files will load the unminified version of the file."><strong><small>Minify files</strong></small></label>';
        $html .= '<input type="checkbox" id="default_minify" name="and_load_default_options[default_minify]" value="1"' . checked( $minify_checkbox_value, 1, false ) . '/>';

        $html .= '</div>'; // end default_minify_setting_container

        //Add jquery dependency to script file
        $jquery_checkbox_value = isset ( $options_default['default_jquery'] ) ? $options_default['default_jquery'] : $args['default_jquery'];
        $html .= '<div id="default_jquery_setting_container" class="default_options_sub_setting">';
        $html .= '<label class="control-label" title="Automatically add jQuery as a dependency to script files"><strong><small>Load jQuery with script files</strong></small></label>';
        $html .= '<input type="checkbox" id="default_jquery" name="and_load_default_options[default_jquery]" value="1"' . checked( $jquery_checkbox_value, 1, false ) . '/>';

        $html .= '</div>'; // end default_jquery_setting_container

        $html .= '</div>'; // end default_general_container

        echo $html;
    }

    function and_load_default_overview_callback($args) {
        $options_default = get_option( 'and_load_default_options' );

        $html = '<div id="default_overview_container">';

        //Overview tab default settings
        $html .= '<div id="default_overview_setting_container" class="default_options_sub_setting">';

        //Overview table default column to sort by
        $html .= '<label class="control-label" title="Default column in overview table to sort by."><strong><small>Default sort column</strong></small></label>';
        $html .= '<select id="overview_default_table_order_column" name="and_load_default_options[overview_default_table_order_column]" class="form-control default_select">';

        $overview_default_table_order_column = isset ( $options_default['overview_default_table_order_column'] ) ? $options_default['overview_default_table_order_column'] : $args['overview_default_table_order_column'];
        $overview_default_table_order_columns = $args['overview_default_table_order_columns'];

        foreach ($overview_default_table_order_columns as $overview_default_table_order_column_name => $overview_default_table_order_column_id) {
            $selected = selected( $overview_default_table_order_column, $overview_default_table_order_column_id, false );
            $html .= '<option value=' . $overview_default_table_order_column_id . $selected . '>' . $overview_default_table_order_column_name . '</option>';
        }

        $html .= '</select>'; // end overview_default_table_order_column

        //Overview table default sort order
        $html .= '<label class="control-label" title="Default column in overview table to sort by."><strong><small>Default sort column</strong></small></label>';
        $html .= '<select id="overview_default_table_sort" name="and_load_default_options[overview_default_table_sort]" class="form-control default_select">';

        $overview_default_table_sort = isset ( $options_default['overview_default_table_sort'] ) ? $options_default['overview_default_table_sort'] : $args['overview_default_table_sort'];
        $overview_default_table_sort_types = $args['overview_default_table_sort_types'];

        foreach ($overview_default_table_sort_types as $overview_default_table_sort_type => $overview_default_table_sort_slug) {
            $selected = selected( $overview_default_table_sort, $overview_default_table_sort_slug, false );
            $html .= '<option value=' . $overview_default_table_sort_slug . $selected . '>' . $overview_default_table_sort_type . '</option>';
        }

        $html .= '</select>'; // end overview_default_table_order_column        

        $html .= '</div>'; // end default_overview_setting_container

        $html .= '</div>'; // end default_overview_container

        echo $html;
    }

    function and_load_default_editor_settings_callback($args) {
        $options_default = get_option( 'and_load_default_options' );

        //Ace editor theme selection
        $editor_default_theme = isset( $options_default['editor_default_theme'] ) ? $options_default['editor_default_theme'] : $args['editor_default_theme'];;
        $editor_themes = $args['editor_themes'];

        $html = '<div class="default_editor_setting_container">';

        $html .= '<div id="editor_default_theme_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label" title="Default editor theme."><strong><small>Default theme</strong></small></label>';
        $html .= '<select id="editor_default_theme" name="and_load_default_options[editor_default_theme]" class="form-control default_select">';

        foreach ($editor_themes as $editor_theme_name => $editor_theme_slug) {
            $selected = selected( $editor_default_theme, $editor_theme_slug, false );
            $html .= '<option value=' . $editor_theme_slug . $selected . '>' . $editor_theme_name . '</option>';
        }

        $html .= "</select>";
        $html .= '</div>'; // end editor_default_theme_setting

        //Ace editor default font size
        $editor_default_font_size = isset( $options_default['editor_default_font_size'] ) ? $options_default['editor_default_font_size'] : $args['editor_default_font_size'];

        $html .= '<div id="editor_default_font_size_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label" title="Default editor font size. Default: 12"><strong><small>Default font size</small></strong></label>';

        //Get all available font sizes and check which one is currently selected
        $editor_font_sizes = $args['editor_font_sizes'];

        $html .= '<select id="editor_default_font_size" name="and_load_default_options[editor_default_font_size]" class="form-control default_select">';
        foreach ($editor_font_sizes as $editor_font_size) {
            $selected = selected( $editor_default_font_size, $editor_font_size, false );
            $html .= '<option value=' . $editor_font_size . $selected . '>' . $editor_font_size . '</option>';
        }
        $html .= "</select>";
        $html .= '</div>'; // end editor_default_font_size_setting

        //Ace editor default mode type
        $editor_default_mode_type = isset ( $options_default['editor_default_mode_type'] ) ? $options_default['editor_default_mode_type'] : $args['editor_default_mode_type'];

        $html .= '<div id="editor_default_mode_type_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label" title="When creating a new file, default to this file type automatically. Default: None"><strong><small>Default type</small></strong></label>';

        //Get all available font sizes and check which one is currently selected
        $editor_mode_types = $args['editor_mode_types'];

        $html .= '<select id="editor_default_mode_type" name="and_load_default_options[editor_default_mode_type]" class="form-control default_select">';
        foreach ($editor_mode_types as $editor_mode_type_name => $editor_mode_type_slug) {
            $selected = selected( $editor_default_mode_type, $editor_mode_type_slug, false );
            $html .= '<option value=' . $editor_mode_type_slug . $selected . '>' . $editor_mode_type_name . '</option>';
        }
        $html .= "</select>";
        $html .= '</div>'; // end editor_default_mode_type_setting

        //Ace editor default tab size override
        $editor_default_tab_size_override = isset ( $options_default['editor_default_tab_size_override'] ) ? $options_default['editor_default_tab_size_override'] : $args['editor_default_tab_size_override'];
        $html .= '<div id="editor_default_tab_size_override_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label" title="Default tab size is file type specific. JavaScript: 4. CSS: 2"><strong><small>Override default tab size</small></strong></label>';
        $html .= '<input type="checkbox" id="editor_default_tab_size_override" name="and_load_default_options[editor_default_tab_size_override]" class="form-control" value="1"' . checked( $editor_default_tab_size_override, 1, false ) . '/>';
        $html .= '</div>'; // end editor_default_tab_size_override_setting

        //Ace editor default tab size
        $editor_default_tab_size = isset ( $options_default['editor_default_tab_size'] ) ? $options_default['editor_default_tab_size'] : $args['editor_default_tab_size'];
        $show_editor_default_tab_size = ( $editor_default_tab_size_override ) ? '' : 'hide-setting'; //show setting section if default tab override is set to true

        $html .= '<div id="editor_default_tab_size_setting" class="default_options_sub_setting ' . $show_editor_default_tab_size . '">';
        $html .= '<label class="control-label" title="Override tab size for all file types."><strong><small>Default tab size</small></strong></label>';
        $html .= '<input type="number" id="editor_default_tab_size" name="and_load_default_options[editor_default_tab_size]" class="form-control default_input" value="' . $editor_default_tab_size . '" />';
        $html .= '</div>'; // end editor_default_tab_size_setting

        //Ace editor default show line numbers
        $editor_default_show_line_numbers = isset ( $options_default['editor_default_show_line_numbers'] ) ? $options_default['editor_default_show_line_numbers'] : $args['editor_default_show_line_numbers'];

        $html .= '<div id="editor_default_show_line_numbers_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label" title="Show line numbers on the left side of editor"><strong><small>Show editor line numbers</small></strong></label>';
        $html .= '<input type="checkbox" id="editor_default_show_line_numbers" name="and_load_default_options[editor_default_show_line_numbers]" value="1"' . checked( $editor_default_show_line_numbers, 1, false ) . '/>';
        $html .= '</div>'; // end editor_default_show_line_numbers_setting

        //Ace editor default print margin
        $editor_default_print_margin = isset ( $options_default['editor_default_print_margin'] ) ? $options_default['editor_default_print_margin'] : $args['editor_default_print_margin'];

        $html .= '<div id="editor_default_show_print_margin_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label" title="Show print margin"><strong><small>Show print margin</strong></small></label>';
        $html .= '<input type="checkbox" id="editor_default_print_margin" name="and_load_default_options[editor_default_print_margin]" value="1"' . checked( $editor_default_print_margin, 1, false ) . '/>';
        $html .= '</div>'; // end editor_default_show_print_margin_setting

        /*Ace editor default print margin column
            hide this section if print margin is disabled */
        $editor_default_print_margin_column = isset ( $options_default['editor_default_print_margin_column'] ) ? $options_default['editor_default_print_margin_column'] : $args['editor_default_print_margin_column'];
        $show_editor_default_print_margin_column = ( $editor_default_print_margin ) ? '' : 'hide-setting'; //show setting section if default tab override is set to true

        $html .= '<div id="editor_default_print_margin_column_setting" class="default_options_sub_setting ' . $show_editor_default_print_margin_column. '">';
        $html .= '<label class="control-label" title="Print margin column width. Default: 80."><strong><small>Print margin column</small></strong></label>';
        $html .= '<input type="number" id="editor_default_print_margin_column" name="and_load_default_options[editor_default_print_margin_column]" class="form-control default_input" value="' . $editor_default_print_margin_column . '" />';
        $html .= '</div>'; // end editor_default_print_margin_column_setting

        $html .= '</div>'; // end default_editor_container

        echo $html;
    }

    /* Edit file tab callbacks */

    function and_load_edit_file_options_callback() {
        $id = ( isset( $_GET['id'] ) ) ? intval( $_GET['id'] ) : false;
        $revision_override = ( isset( $_GET['revision'] ) ) ? intval( $_GET['revision'] ) : false;

        $options_default = get_option( 'and_load_default_options' );
        $editor_default_mode_type = $options_default['editor_default_mode_type'];

        if($id) { //check if file id is supplied and load the content
            try {
                global $wpdb;
                $table_name = $wpdb->prefix . 'and_load'; 

                $sql = "SELECT name,slug,type,revision,srcpath,minpath FROM ".$table_name." WHERE id = '".$id."' LIMIT 1";
                $result = $wpdb->get_results($sql, ARRAY_A);
            } catch(Exception $e) {
                $error_id = isset( $error_id ) ? $error_id : 15; //could not find file with id
                $result = NULL;
            }

            if($result) {
                extract( $result[0] ); //turn array into named variables, see $sql SELECT query above for variable names

                //Check for revision override
                if($revision_override !== false) {
                    if($revision_override <= $revision AND $revision_override > 0) {
                        $current_revision = $revision_override;
        
                        $srcname = basename($srcpath, $type);
                        $srcpath_base = dirname($srcpath) . '/';
                        $srcpath = $srcpath_base . $srcname . $current_revision . "." . $type;
                    } else {
                         $current_revision = 0;
                    }
                } else {
                    if($revision > 0) {
                        $current_revision = $revision;

                        $srcname = basename($srcpath, $type);
                        $srcpath_base = dirname($srcpath) . '/';
                        $srcpath = $srcpath_base . $srcname . $current_revision . "." . $type;
                    } else {
                        $current_revision = 0;
                    } // end if
                } //end if

                //File save submit button
                $html = '<p class="submit"><input name="submit" id="submit" class="btn btn-lg btn-success" value="&#x2714; Save file" type="submit"></p>';

                //File delete submit button
                $html .= '<p class="delete"><input id="delete" class="btn btn-danger" name="delete" type="submit" value="&#x2716; Delete" /></p>';

                $html .= '<div id="edit_file_input_container">';

                $html .= '<label class="control-label">File: </label><p>' . $name . '</p>';

                // Shortcode displayed in an input field

                $html .= '<label class="control-label">Shortcode:</label>';

                $shortcode_display = ($current_revision == $revision) ? 'and_load id=' . $id : 'and_load id=' . $id . ' revision_override=' . $current_revision;

                $html .= '<input type="text" id="edit_file_shortcode_display" class="form-control edit_file_input" name="and_load_edit_file_options[edit_file_shortcode_display]" readonly=readonly value="['.$shortcode_display.']"/>';

                // Select revision dropdown

                $html .= '<label class="control-label">Current revision:</label>';
                $html .= '<select id="edit_file_revisions_select" class="form-control edit_file_select" name="edit_file_revisions_select">';
                for ($i = $revision; $i >= 0; $i--) {
                    $selected = selected( $current_revision, $i, false );

                    $revision_name = ( $i > 0 AND $i == $revision ) ? 'Latest' : ( ($i == 0) ? 'Source' : $i );

                    $html .= '<option value='.$i.$selected.'>' . $revision_name .'</option>';
                }
                $html .= '</select>';

                $html .= '<div id="edit_file_more_settings_button" class="btn btn-sm btn-default"><span id="edit_file_more_icon" class="glyphicon glyphicon-collapse-down"></span><span id="edit_file_more_text">More</span></div>';

                $html .= '</div>'; // end edit_file_input_container

                //Allow user to override editor settings temporarily for this editing session
                $editor_default_options = $this->and_load_get_options_default_editor();

                $editor_default_font_size = $options_default['editor_default_font_size'];
                $editor_default_theme = $options_default['editor_default_theme'];

                $editor_font_sizes = $editor_default_options['editor_font_sizes'];
                $editor_themes = $editor_default_options['editor_themes'];

                $html .= '<div id="edit_file_editor_settings_container">';

                //Override font size
                $html .= '<label class="control-label">Font size:</label>';
                $html .= '<select name="edit_file_font_size_select" id="edit_file_font_size_select" class="form-control edit_file_select">';

                foreach ($editor_font_sizes as $editor_font_size) {
                    $selected = selected( $editor_default_font_size, $editor_font_size, false );
                    $html .= '<option value=' . $editor_font_size . $selected . '>' . $editor_font_size . '</option>';
                }

                $html .= '</select>'; //end edit_file_font_size_select

                //Overide editor theme
                $html .= '<label class="control-label"><strong><small>Theme</strong></small></label>';
                $html .= '<select id="edit_file_theme_select" name="edit_file_theme_select" class="form-control edit_file_select">';

                foreach ($editor_themes as $editor_theme_name => $editor_theme_slug) {
                    $selected = selected( $editor_default_theme, $editor_theme_slug, false );
                    $html .= '<option value=' . $editor_theme_slug . $selected . '>' . $editor_theme_name . '</option>';
                }

                $html .= "</select>"; //end edit_file_theme_select

                $html .= '</div>'; //end edit_file_editor_settings_container

                //Load file content
                $content = $this->and_load_get_file( $srcpath );

                //We need the id to refer to later, save this to a simple input field
                $html .= '<input type="text" id="edit_file_current_id" name="and_load_edit_file_options[edit_file_current_id]" value="' . ( isset( $_GET['id'] ) ? ( intval ( $_GET['id'] ) ) : false ) . '"/>';

                if($content !== false) {
                    //init editor with content

                    /*Create a textarea to temporarily hold the raw data from Ace editor
                    this data will then be processed when the page is reloaded again (Save Changes button is clicked)
                    The textarea will be continously updated with javascript
                    */
                    $html .= '<textarea id="edit_file_temporary_textarea" name="and_load_edit_file_options[edit_file_temporary_textarea]">' . htmlspecialchars($content) .  '</textarea>';

                    echo $html;

                    $this->and_load_editor_init( '', $type );
                } else {

                    $content = 'File content could not be loaded! Please report this error to the developer!';

                    $html .= '<textarea id="edit_file_temporary_textarea" name="and_load_edit_file_options[edit_file_temporary_textarea]">' . $content .  '</textarea>';

                    echo $html;

                    $this->and_load_editor_init( false, $editor_default_mode_type );
                } // end if
            } else { //A file with the corresponding ID could not be found in the database
                $html = '<div id="edit_file_invalid_file_id_container" class="bg-danger">';

                $html .= '<h4>Error!</h4>';
                $html .= '<p class="invalid_file_id">No file with ID <strong>' . $id . '</strong> was found in the database.</p>';
                $html .= '<a id="invalid_file_return_link" class="btn btn-lg btn-default" href="?page=and_load&tab=tab_overview">Click here to return to the overview</a>';

                $html .= '</div>'; // end edit_file_invalid_file_id_container

                echo $html;
            }// end if
        } else {
            //No file is selected, this is a new file

            //File save submit button
            $html = '<p class="submit"><input name="submit" id="submit" class="btn btn-success" value="&#x2714; Save file" type="submit"></p>';

            $html .= '<p id="container_help_text"><span id="help-title">Tip!</span><span id="help_text">Start by entering a filename and a file type, then enter the content into the editing area. Alternatively, select a file for upload.</span></p>';

            $html .= '<div id="edit_file_input_container">';

            //File name input
            $html .= '<label class="control-label">File name</label>';
            $html .= '<input type="text" id="new_file_name" class="form-control edit_file_input" name="and_load_edit_file_options[new_file_name]" placeholder="Enter a file name" />';

            //File type select
            $html .= '<label class="control-label">File type</label>';
            $html .= '<select id="new_file_type" class="form-control edit_file_select" name="and_load_edit_file_options[new_file_type]"><option selected=selected value="plain_text">File type</option><option value="javascript">JavaScript</option><option value="css">CSS</option></select>';

            //File upload
            $html .= '<div id="edit_file_file_upload_container">';

            $html .= '<div id="new_file_upload_reset_button">&#x2716;</div>';
            $html .= '<input type="text" id="new_file_upload_file_name" class="form-control edit_file_input" disabled="disabled" placeholder="Select File..." />';

            $html .= '<div id="new_file_upload_button" class="btn btn-primary"><span>Upload File</span>';
            $html .= '<input type="file" id="new_file_upload" name="and_load_edit_file_options[new_file_upload]" accept=".js,.css,.txt" />';
            $html .= '</div>'; // end new_file_upload_button

            $html .= '</div>'; // end edit_file_file_upload_container

            $html .= '</div>'; // end edit_file_input_container

            /*Create a textarea to temporarily hold the raw data from Ace editor
            this data will then be processed when the page is reloaded again (Save Changes button is clicked)
            The textarea will be continously updated with javascript
            */
            $html .= '<textarea id="edit_file_temporary_textarea" name="and_load_edit_file_options[edit_file_temporary_textarea]"></textarea>';

            //We also need the id to refer to later, save this to a simple input field as well
            $html .= '<input type="text" id="edit_file_current_id" name="and_load_edit_file_options[edit_file_current_id]" value="' . ( isset( $_GET['id'] ) ? ( intval ( $_GET['id'] ) ) : false ) . '"/>';

            echo $html;

            $this->and_load_editor_init( false, $editor_default_mode_type );
        } // end if
    }

    /* Help tab callbacks */

    function and_load_help_callback() {
        echo $this->templates->render('tab_help');
    }


    /**************************
    * Sanitization functions  *
    * For wordpress callbacks *
    * and plugin usage        *
    **************************/

    function and_load_filter_string($string) {
        $string = preg_replace("/[^a-zA-Z0-9]+/", "", $string);
        return $string;
    }

    function and_load_default_options_callback_sanitize($args) {
        $options_default = get_option( 'and_load_default_options' );
        
        //Checkboxes
        $options_default['default_minify'] = isset ( $args['default_minify'] ) ? $args['default_minify'] : false;
        $options_default['default_jquery'] = isset ( $args['default_jquery'] ) ? $args['default_jquery'] : false;
        $options_default['editor_default_print_margin'] = isset ( $args['editor_default_print_margin'] ) ? $args['editor_default_print_margin'] : false;
        $options_default['editor_default_show_line_numbers'] = isset ( $args['editor_default_show_line_numbers'] ) ? $args['editor_default_show_line_numbers'] : false;
        $options_default['editor_default_tab_size_override'] = isset ( $args['editor_default_tab_size_override'] ) ? $args['editor_default_tab_size_override'] : false;

        //Drop downs
        $options_default['editor_default_theme'] = isset ( $args['editor_default_theme'] ) ? $args['editor_default_theme'] : $options_default['editor_default_theme'];
        $options_default['editor_default_font_size'] = isset ( $args['editor_default_font_size'] ) ? $args['editor_default_font_size'] : $options_default['editor_default_font_size'];
        $options_default['editor_default_mode_type'] = isset ( $args['editor_default_mode_type'] ) ? $args['editor_default_mode_type'] : $options_default['editor_default_mode_type'];
        $options_default['overview_default_table_sort'] = isset ( $args['overview_default_table_sort'] ) ? $args['overview_default_table_sort'] : $options_default['overview_default_table_sort'];
        $options_default['overview_default_table_order_column'] = isset ( $args['overview_default_table_order_column'] ) ? $args['overview_default_table_order_column'] : $options_default['overview_default_table_order_column'];

        //Inputs
        $options_default['editor_default_print_margin_column'] = isset ( $args['editor_default_print_margin_column'] ) ? $args['editor_default_print_margin_column'] : $options_default['editor_default_print_margin_column'];
        $options_default['editor_default_tab_size'] = isset ( $args['editor_default_tab_size'] ) ? $args['editor_default_tab_size'] : $options_default['editor_default_tab_size'];

        return $options_default;
    }

    function and_load_edit_file_callback_sanitize($args) {
        //Get the default options
        $options_default = get_option( 'and_load_default_options' );
        $minify = $options_default['default_minify'];

        //Get the file name, content and type
        $file_name = ( isset( $args[ 'new_file_name' ] ) ) ? $args[ 'new_file_name' ] : NULL;
        $file_content = ( isset ( $args[ 'edit_file_temporary_textarea' ] ) ) ? $args[ 'edit_file_temporary_textarea' ] : NULL;
        $file_type = ( isset ( $args[ 'new_file_type' ] ) ) ? $args[ 'new_file_type' ] : NULL;

        $id = ( isset ( $args['edit_file_current_id'] ) ) ? $args['edit_file_current_id'] : NULL;
        $request = ( isset( $_POST['submit']) ) ? 'save' : ( ( isset( $_POST['delete'] ) ) ? 'delete' : NULL );
       
        $file_datas = array();

        if( ! ( is_null($request) ) ) {
            if( ! ( empty( $id ) ) ) {
                if($request == 'delete') {
                    try {
                        $file_datas[] = $this->and_load_delete_file( $id );
                    } catch (Exception $e) {
                        //var_dump($e);
                        $operation = 'delete';
                        $error_id = isset( $error_id ) ? $error_id : $e->getCode();
                    }
                } else {
                    try {
                        $file_datas[] = $this->and_load_add_file_revision($id, $file_content, $minify);
                    } catch (Exception $e) {
                        //var_dump($e);
                        $operation = 'update';
                        $error_id = isset( $error_id ) ? $error_id : $e->getCode();
                    }
                }
            } else {
                //Check if a file has been selected for upload
                $tmp_file_path = $_FILES['and_load_edit_file_options']['tmp_name']['new_file_upload'];
                $file_tmp = ( $tmp_file_path ) ? $tmp_file_path : false;

                if($file_tmp) { //file is being uploaded
                    try {
                        $file_content = file_get_contents( $file_tmp  ); //get the raw content from the uploaded file

                        $file_datas[] = $this->and_load_save_to_database(
                            array(
                                'content' => $file_content,
                                'name' => $file_name,
                                'type' => $file_type,
                                'minify' => $minify
                            )
                        );

                        //Go over every file that was uploaded and set operation to 'uploaded'
                        for ($i=0; $i < sizeof($file_datas); $i++) { 
                            $file_datas[$i]['operation'] = 'uploaded';
                        }
                    } catch (Exception $e) {
                        //var_dump($e);
                        $operation = 'uploaded';
                        $error_id = isset( $error_id ) ? $error_id : $e->getCode();
                    }
                } elseif( ! (empty( $file_name ) ) ) { //new file, save it
                    try {
                        $file_datas[] = $this->and_load_save_to_database(
                            array(
                                'content' => $file_content,
                                'name' => $file_name,
                                'type' => $file_type,
                                'minify' => $minify
                            )
                        );
                    } catch(Exception $e) {
                        //var_dump($e);
                        $operation = 'saved';
                        $error_id = isset( $error_id ) ? $error_id : $e->getCode();
                    }
                } else {
                    $error_id = isset( $error_id ) ? $error_id : 22;
                    $operation = 'saved';
                }
            }

            if( ! ( isset( $error_id ) ) ) {
                $this->and_load_add_settings_message( $file_datas );
            } else {
                $this->and_load_add_settings_message(array(
                    array(
                        'success' => false,
                        'error_id' => $error_id,
                        'operation' => $operation
                    ))
                );
            }
        }
    }

    /*
    * Used by sanitization functions to display messages to user via Settings API
    * $args = array( 'success' => bool, ['id' => id, 'name' => name] )
    */

    function and_load_add_settings_message($array) {
        foreach ($array as $file_data) {
            if($file_data['success'] == true){
                
                $location = admin_url('options-general.php?page=and_load&tab=tab_edit&id='.$file_data['id'] );
                wp_redirect( $location );
                exit;

                /* TODO, figure out if there's a way to do a redirect and then do an add_settings_error

                $message_setting = 'file_update';
                $message_setting_slug = 'file_update';
                $message_type = 'updated';
                
                $message = $file_data['type'] . ' file <em>'.$file_data['name'].'</em> has been ' . $file_data['operation'] . ' successfully!';
                */
            } elseif($file_data['success'] == false) {
                $message_setting = 'file_update';
                $message_setting_slug = 'file_update';
                $message_type = 'error';
                $location = admin_url('options-general.php?page=and_load&tab=tab_help&error_id=error_id_'. $file_data['error_id'] );

                $message = 'File could not be ' . $file_data['operation'] . '! <a href="' . $location . '" target="_blank">Click here for more info.</a>';
            }
        }

        try {
            add_settings_error($message_setting, $message_setting_slug, $message, $message_type);
        } catch (Exception $e) {
            //var_dump($e);
        }
    }

    /** and_load_editor_init
    * Configures Ace editor with content and sets up environment according to default options
    *
    * @content - (string) or (bool)
    * @mode_type - (string) 'js|css|plain_text'
    */

    function and_load_editor_init($content, $mode_type) {
        $options_default = get_option( 'and_load_default_options' );

        //Ace default editor settings
        extract( $options_default);

        if($content !== false) { //if an existing file is loaded, set the file's type as specified
            switch ($mode_type) {
                case 'js':
                    $editor_mode_type = 'javascript';
                    break;
                default:
                    $editor_mode_type = $mode_type;
                    break;
            }        foreach ($this->e($header) as $key => $value) {
                echo '<th class="' . $key . '">' . $value . '</th>';
            }
        } else { //this is a new file, load the default mode type
            switch ($editor_default_mode_type) {
                case 'js':
                    $editor_mode_type = 'javascript';
                    break;
                default:
                    $editor_mode_type = $editor_default_mode_type;
                    break;
            }            
        } //end if

        //Build Ace editor
        $container = '<div class="editor_container">';
        $editor = '<div id="editor">'.$content.'</div>';
        $container .= $editor . '</div>';

        echo $container;

        ?>
            <script>
                var editor;
                var editorSettings = {
                    mode:"<?php echo $editor_mode_type; ?>",
                    fontSize:"<?php echo $editor_default_font_size; ?>",
                    tabOverride:"<?php echo $editor_default_tab_size_override; ?>",
                    tabSize:"<?php echo $editor_default_tab_size; ?>",
                    theme:"<?php echo $editor_default_theme; ?>",
                    showPrintMargin:"<?php echo $editor_default_print_margin; ?>",
                    printMarginColumn:"<?php echo $editor_default_print_margin_column; ?>",
                    showLineNumbers:"<?php echo $editor_default_show_line_numbers; ?>"
                };
            </script>
        <?php
    } //end and_load_editor_init

    function and_load_enqueue_file_options($name, $file_path, $is_script = false) {
        if( class_exists('AndLoad') ) {
            $dependencies = ( $is_script ) ? 'jquery' : false;
            AndLoad::and_load_enqueue_file($name, $file_path, $is_script, $dependencies );
        } // end if
    } // end and_load_enqueue_file_options

    function and_load_options_page(  ) {

        //Load default styles for all tabs

        wp_enqueue_style('fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.2/css/font-awesome.min.css');
        wp_enqueue_style('bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
        wp_enqueue_style('open-sans', '//fonts.googleapis.com/css?family=Open+Sans');
    
        $this->and_load_enqueue_file_options( 'and_load_admin-style', 'css/admin.css' );

        if( isset( $_GET[ 'tab' ] ) ) {  
            $active_tab = $_GET[ 'tab' ];  
        } else {
            $active_tab = 'tab_overview';
        } // end if

        $files = sizeof($this->and_load_get_scripts_styles());

        ?>
        <div class="wrap">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="<?php echo $active_class = ($active_tab == 'tab_overview') ? 'active' : '' ?>">
                    <a href="#tab_overview" class="tab_overview" data-toggle="tab">
                        <span class="fa fa-list"></span> Overview <span class="badge"><?php echo ($files > 0) ? $files : '' ; ?></span>
                    </a>
                </li>
                <li role="presentation" class="<?php echo $active_class = ($active_tab == 'tab_default') ? 'active' : '' ?>">
                    <a href="?page=and_load&amp;tab=tab_default" class="tab_default <?php echo $active_class = ($active_tab == 'tab_default') ? 'active_tab' : '' ?>">
                        <span class="fa fa-cogs"></span> Settings
                    </a>
                </li>
                <li role="presentation" class="<?php echo $active_class = ($active_tab == 'tab_edit') ? 'active' : '' ?>">
                    <a href="?page=and_load&amp;tab=tab_edit" class="tab_edit <?php echo $active_class = ($active_tab == 'tab_edit') ? 'active_tab' : '' ?>">
                        <span class="fa fa-pencil"></span> Editor</a>
                </li>
                <li role="presentation" class="<?php echo $active_class = ($active_tab == 'tab_help') ? 'active' : '' ?>">
                    <a href="?page=and_load&amp;tab=tab_help" class="tab_help">
                        <span class="fa fa-question"></span> Help
                    </a>
                </li>
            </ul>

            <form id="and_load_form" action='options.php' method='post' enctype='multipart/form-data'>
                
                <?php

                if($active_tab == 'tab_overview') {
                    //Libraries
                    $this->and_load_enqueue_file_options( 'datatables-style-bootstrap', 'lib/datatables/css/dataTables.bootstrap.css', false );
                    $this->and_load_enqueue_file_options( 'datatables-script', 'lib/datatables/js/jquery.dataTables.min.js', true );
                    $this->and_load_enqueue_file_options( 'datatables-script-bootstrap', 'lib/datatables/js/dataTables.bootstrap.min.js', true );

                    //Tab styles and scripts
                    $this->and_load_enqueue_file_options( 'tab_overview_js', 'js/tab_overview.js', true );
                    $this->and_load_enqueue_file_options( 'tab_overview_css', 'css/tab_overview.css', false );

                    //Tab sections and fields 
                    settings_fields( 'and_load_overview' );
                    do_settings_sections( 'and_load_overview' );

                } elseif($active_tab == 'tab_default') {
                    //Tab styles and scripts
                    $this->and_load_enqueue_file_options( 'tab_default_css', 'css/tab_default.css', false );
                    $this->and_load_enqueue_file_options( 'tab_default_js', 'js/tab_default.js', true );

                    //Tab sections and fields 
                    settings_fields( 'and_load_default_options' );
                    do_settings_sections( 'and_load_default_options' );

                    submit_button();
                } elseif($active_tab == 'tab_edit') {
                    //Tab styles and scripts
                    $this->and_load_enqueue_file_options( 'tab_edit_css', 'css/tab_edit.css', false );
                    $this->and_load_enqueue_file_options( 'tab_edit_js', 'js/tab_edit.js', true );

                    //Libraries
                    $this->and_load_enqueue_file_options( 'ace-js', 'lib/ace-builds/js/ace.js', true );
                    $this->and_load_enqueue_file_options( 'bootstrap-js', 'lib/bootstrap/js/bootstrap.min.js', true );
                    $this->and_load_enqueue_file_options( 'bootbox-js', 'lib/bootbox/js/bootbox.js', true );

                    //Tab sections and fields 
                    settings_fields( 'and_load_edit_file_options' );
                    do_settings_sections( 'and_load_edit_file_options' );

                } elseif($active_tab == 'tab_help') {
                    //Tab styles and scripts
                    $this->and_load_enqueue_file_options( 'tab_help_css', 'css/tab_help.css', false );
                    $this->and_load_enqueue_file_options( 'tab_help_js', 'js/tab_help.js', true );

                    //Tab sections and fields 
                    $this->and_load_help_callback();
                } // end if

                ?>
                
            </form>
        </div><!-- end wrap -->

        <?php
    }
}

?>