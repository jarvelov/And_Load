<?php

Class ShortcodeLoad_Options extends ShortcodeLoad {

    function __construct() {
        add_action( 'admin_menu', array($this, 'shortcode_load_add_admin_menu') );
        add_action( 'admin_init', array($this, 'shortcode_load_settings_init') );
    }

    function shortcode_load_add_admin_menu(  ) { 

        add_options_page( 'Shortcode Load', 'Shortcode Load', 'manage_options', 'shortcode_load', array($this, 'shortcode_load_options_page') );

    }

    function shortcode_load_settings_init(  ) {

        if ( ! current_user_can('update_plugins') )
            return;

        /* Overview section */

        add_settings_section( 
            'shortcode_load_overview',
            'Registered scripts and styles',
            array($this, 'shortcode_load_overview_callback'),
            'shortcode_load_overview'
        );    

        register_setting('shortcode_load_overview', 'shortcode_load_overview');

        /* Default settings section */

        add_settings_section( 
            'shortcode_load_default',
            'Default Settings',
            array($this, 'shortcode_load_default_options_callback'),
            'shortcode_load_default_options'
        );

        add_settings_field(
            'shortcode_load_automatically_minify',
            'Auto minify',
            array($this, 'shortcode_load_default_automatically_minify_callback'),
            'shortcode_load_default_options',
            'shortcode_load_default',
            array('default' => 1) //set default to auto minify for all file types
        );

        add_settings_field(
            'shortcode_load_default_editor_settings',
            'Editor settings',
            array($this, 'shortcode_load_default_editor_settings_callback'),
            'shortcode_load_default_options',
            'shortcode_load_default',
            array(
                'editor_default_theme' => 'monokai',
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
                'editor_font_sizes' => array(8, 10, 12, 14, 16, 18, 20, 22, 24),
                'editor_mode_types' => array('JavaScript' => 'javascript', 'CSS' => 'css', 'Text' => 'plain_text'),
                'editor_default_font_size' => 12,
                'editor_default_tab_size' => 4,
                'editor_default_mode_type' => 'plain_text',
                'editor_default_print_margin' => true,
                'editor_default_print_margin_column' => 80,
                'editor_default_show_line_numbers' => true
            )

        );

        register_setting('shortcode_load_default_options', 'shortcode_load_default_options');

        /* Edit file section */

        add_settings_section( 
            'shortcode_load_edit_file',
            '',
            array($this, 'shortcode_load_edit_file_options_callback'),
            'shortcode_load_edit_file_options'
        );

        add_settings_field(
            'shortcode_load_edit_file',
            '',
            array($this, 'shortcode_load_edit_file_source_options_callback'),
            'shortcode_load_edit_file_options',
            'shortcode_load_edit_file'
        );

        register_setting('shortcode_load_edit_file_options', 'shortcode_load_edit_file_options', array($this, 'shortcode_load_edit_file_callback_sanitize'));

        /* Edit file section */

        add_settings_section( 
            'shortcode_load_help',
            'Help',
            array($this, 'shortcode_load_help_callback'),
            'shortcode_load_help_section'
        );

        add_settings_field(
            'shortcode_load_help_documentation',
            'Documentation',
            array($this, 'shortcode_load_help_documentation_callback'),
            'shortcode_load_help_section',
            'shortcode_load_help'
        );

        add_settings_field(
            'shortcode_load_help_credits',
            'Credits',
            array($this, 'shortcode_load_help_credits_callback'),
            'shortcode_load_help_section',
            'shortcode_load_help'
        );        

        register_setting('shortcode_load_help_section', 'shortcode_load_help_section');


    }

    /* 
    * Save a new script or style to the database
    */
    function shortcode_load_save_to_database($args) {
        try {
            $db_args = $this->shortcode_load_save_file($args);
        } catch (Exception $e) {
            //var_dump($e);
            $error_id = $e->getCode();
        }

        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'shortcode_load'; 

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
            $error_id = 1; //error writing to database
        }

        if( ( ! isset($error_id) ) ) {
            $name = $db_args['name'] . '.' . $db_args['type'];
            $type = ($db_args['type'] == 'js') ? 'Script' : 'Style';
            $return_args = array('success' => true, 'id' => $id, 'name' => $name, 'type' => $type, 'operation' => 'saved');
        } else {
            $return_args = array('success' => false, 'error_id' => $error_id);
        }

        return $return_args;
    }

    function shortcode_load_update_database_record($args) {
        extract($args);

        $return_args = array('success' => false);

        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'shortcode_load';

            $result = $wpdb->update( 
                $table_name, 
                array( 
                    'revision' => $revision,    // int
                    'updated_timestamp' => current_time('mysql', 1),
                ), 
                array( 
                    'ID' => $id
                ), 
                array(
                    '%d', //revision
                    '%s' //updated_timestamp
                ),
                array('%d') //id
            );

            if($result > 0) {
                $return_args['success'] = true;
            } else {
                $return_args['success'] = false;
            }

        } catch (Exception $e) {
            //var_dump($e);
            $return_args['success'] = false;
        }

        return $return_args;
    }

    /*
    * Save a new script or style to a file in wordpress' uploads folder
    */
    function shortcode_load_save_file($args) {
        $wp_uploads_path = wp_upload_dir();
        $uploads_dir = $wp_uploads_path['basedir'] . '/shortcode_load/';

        $minify = $args['minify'];
        $content = $args['content'];
        $org_name = $args['name'];

        $type = ( $args['type'] == 'javascript' ) ? 'js' : $args['type'];

        $src_dir = $uploads_dir . $type . '/src/';
        $min_dir = $uploads_dir . $type . '/min/';
        
        $random5 = substr(md5(microtime()),rand(0,26),5); //generate 5 random characters to ensure filename is unique
        $slug = $org_name . '.' . $random5;

        $slug = $this->shortcode_load_filter_string($slug); //filter out any characters we don't want in path

        $file_src = $src_dir . $slug . '.' . $type;

        if (!is_dir($src_dir)) {
            wp_mkdir_p($src_dir);
        }

        if($minify == true) {
            if (!is_dir($min_dir)) {
                wp_mkdir_p($min_dir);
            }
        }

        if($type == 'js') {
            $file_args = $this->shortcode_load_save_file_js($file_src, $content, $minify);
        } elseif($type == 'css') {
            $file_args = $this->shortcode_load_save_file_css($file_src, $content, $minify);
        } else {
            throw new Exception("Unknown file type", 0); //0 = could not write file to local path specified. Check path and permissions.
            
        }

        $db_args = array('name' => $org_name, 'slug' => $slug, 'type' => $type, 'srcpath' => $file_args['srcpath'], 'minify' => $minify, 'minpath' => $file_args['minpath']);

        return $db_args;
    }

    /*
    * Save javascript content to path,
    * optionally save a minified version
    */
    function shortcode_load_save_file_js($path, $content, $minify) {
        $file_args_array = array('success' => NULL);

        try {
            file_put_contents($path, $content);
            $file_args_array['srcpath'] = $path;
        } catch (Exception $e) {
            //var_dump($e);
            $file_args_array['success'] = false;
        }

        try {
            if($minify == true) {
                $minified_content = $this->shortcode_load_minify_js($content);
                $slug = basename($path, '.js');
                $path_min = dirname(dirname($path)) . '/min/' . $slug . '.min.js';
                $file_args_array['minpath'] = $path_min;

                file_put_contents($path_min, $minified_content);
            } else {
                $file_args_array['minpath'] = "";
            }
        } catch (Exception $e) {
            //var_dump($e);
            $file_args_array['success'] = false;
        }

        if($file_args_array['success'] !== false) {
            $file_args_array['success'] = true;
        }

        return $file_args_array;
    }

    /*
    * Save css content to path,
    * optionally save a minified version
    */
    function shortcode_load_save_file_css($path, $content, $minify) {
        $file_args_array = array('success' => NULL);

        try {
            file_put_contents($path, $content);
            $file_args_array['srcpath'] = $path;
        } catch (Exception $e) {
            //var_dump($e);
            $file_args_array['success'] = false;
        }

        try {
            if($minify == true) {
                $minified_content = $this->shortcode_load_minify_js($content);
                $slug = basename($path, '.css');
                $path_min = dirname(dirname($path)) . '/min/' . $slug . '.min.css';
                $file_args_array['minpath'] = $path_min;

                file_put_contents($path_min, $minified_content);
            } else {
                $file_args_array['minpath'] = "";
            }
        } catch (Exception $e) {
            //var_dump($e);
            $file_args_array['success'] = false;
        }

        if($file_args_array['success'] !== false) {
            $file_args_array['success'] = true;
        }

        return $file_args_array;
    }

    /*
    * Add a new revision of a file
    */

    function shortcode_load_add_file_revision($args) {
        extract($args); //turn $args array into named variables

        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'shortcode_load'; 

            $sql = "SELECT name,slug,type,revision,srcpath,minify FROM ".$table_name." WHERE id = ".(int)$id." LIMIT 1";
            $result = $wpdb->get_results($sql, ARRAY_A)[0];
        } catch (Exception $e) {
            //var_dump($e);
            $error_id = 2; //2 = database lookup error, does entry with $id exist?
        }

        extract($result); //extract array to named variables, see $sql SELECT query above for variable names

        $new_revision = ( intval($revision) + 1);

        $srcname = basename($srcpath, $type);
        $unique_suffix = str_replace($slug, "", $srcname);
        $new_slug = $slug . $unique_suffix . $new_revision . "." . $type;

        $file_src_base = dirname($srcpath) . '/';
        $file_src = $file_src_base . $new_slug;

        if($type == 'js') {
            $file_args = $this->shortcode_load_save_file_js($file_src, $content, $minify);
        } elseif($type == 'css') {
            $file_args = $this->shortcode_load_save_file_css($file_src, $content, $minify);
        } else {
            $file_args = NULL;
            $error_id = 3; //3 = invalid file type specified, column type for row with id in database is malformed
        }

        if($file_args['success'] == true) {
            $result = $this->shortcode_load_update_database_record( array('id' => (int)$id,'revision' => $new_revision));

            if($result['success'] == true) {
                $type = ($type == 'js') ? 'Script' : 'Style';
                $return_args = array('success' => true, 'id' => $id, 'name' => $name, 'type' => $type, 'operation' => 'updated');
            } else {
                $return_args = array('success' => false, 'error_id' => $error_id);
            }
        } else {
            $return_args = array('success' => false, 'error_id' => $error_id);
        }

        return $return_args;
    }

    /*
    * Minify javascript code
    */
    function shortcode_load_minify_js($content) {
        $minified_content = $content;
        return $minified_content;
    }

    /*
    * Minify css code
    */
    function shortcode_load_minify_css($content) {
        $minified_content = $content;
        return $minified_content;
    }

    /*
    * Return all saved entries of type 'js' in database
    */
    function shortcode_load_get_scripts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shortcode_load'; 

        $sql = "SELECT id,name,slug,revision,updated_timestamp,created_timestamp FROM ".$table_name." WHERE type = 'js' ORDER BY created_timestamp DESC";
        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    }

    /*
    * Return all entries of type 'css' in database
    */
    function shortcode_load_get_styles() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shortcode_load'; 

        $sql = "SELECT id,name,slug,revision,updated_timestamp,created_timestamp FROM ".$table_name." WHERE type = 'css' ORDER BY created_timestamp DESC";
        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    }

    /*
    * Return all saved entries regardless of type in database
    */
    function shortcode_load_get_scripts_styles() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shortcode_load'; 

        $sql = "SELECT id,name,slug,type,revision,updated_timestamp,created_timestamp FROM ".$table_name." ORDER BY created_timestamp DESC";
        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    }   

    /*
    * Returns a file's content.
    * If the file isn't found bool(false) is returned.
    * @path     Path to file
    */

    function shortcode_load_get_file($path) {
        if( file_exists( $path ) ) {
            try {
                $content = file_get_contents($path);
            } catch (Exception $e) {
                $content = "Error reading file. Verify file integrity and permissions. Local path: ".$path;
            }
        } else {
            $content = false;
        }

        return $content;
    }

    /*
    * Callbacks
    **/

    /* Overview tab callbacks */

    function shortcode_load_overview_callback() {
        echo '<p>Overview of the currently registered scripts and styles</p>'; 

        $files = $this->shortcode_load_get_scripts_styles();

        $html = '<a id="new_file_button" class="btn btn-block btn-sm btn-default" href="?page=shortcode_load&amp;tab=tab_edit">New File &raquo;</a>';
        $html .= '<div id="overview_container">';

        if(sizeof($files) > 0) {
            $html .= '<p id="overview_help_text"><span id="help-title">Tip!</span><span id="help_text">Click the name of the file in the table to view/edit it.</span></p>';
            $html .= '<div class="shortcode_load_table_container">';
            $html .= '<table id="overview_table" class="table table-hover table-striped table-bordered display">';
            $html .= '<thead><th>Id</th><th>Type</th><th>Name</th><th>Revisions</th><th>Last Updated</th><th>Created</th></thead>';
            $html .= '<tbody>';

            foreach ($files as $file) {
                extract($file); //id, name, slug, type, revision, updated_timestamp, created_timestamp

                $html .= '<tr>';
                $html .= '<td>' . $id . '</td>';
                $html .= '<td>' . strtoupper($type) . '</td>';
                $html .= '<td ><a href="?page=shortcode_load&amp;tab=tab_edit&amp;id=' . $id . '" title="">' . $name . '</a></td>';
                $html .= '<td>' . $revision . '</td>';
                $html .= '<td>' . $updated_timestamp . '</td>';
                $html .= '<td>' . $created_timestamp . '</td>';

                $html .= '</tr>';
            }

            $html .= '</tbody></table></div>';
        } else {
            $html .= '<h2>No scripts or styles created yet!</h2>';
            $html .= '<p>To begin click the <em>"New file &raquo;"</em> button or the <strong><a href="?page=shortcode_load&amp;tab=tab_edit">"Edit file"</a></strong> tab above.</p>';
            $html .= '<p>For more info and help check out the <strong><a href="?page=shortcode_load&amp;tab=tab_help">Help</a></strong> tab</p>';
        }

        $html .= '</div>';

        echo $html;        
    }

    /* Default options tab callbacks */

    function shortcode_load_default_options_callback() {
        echo '<p>Default options to configure editor settings and minify behaviour.</p>'; 
    }

    function shortcode_load_default_automatically_minify_callback($args) {
        $options_default = get_option( 'shortcode_load_default_options' );
        $default_value = $args['default'];

        $default_minify_checkbox = isset ( $options_default['default_minify_checkbox'] ) ? $options_default['default_minify_checkbox'] : $default_value;

        $html = '<div id="default_minify_setting_container" class="default_options_sub_setting">';

        $html .= '<label class="control-label"><strong><small>Automatically minify files</strong></small></label>';
        $html .= '<input type="checkbox" id="default_minify_checkbox" name="shortcode_load_default_options[default_minify_checkbox]" value="1"' . checked( $default_minify_checkbox, 1, false ) . '/>';

        $html .= '</div>'; // ./default_minify_setting_container

        echo $html;
    }

    function shortcode_load_default_editor_settings_callback($args) {
        $options_default = get_option( 'shortcode_load_default_options' );

        //Ace editor theme selection
        $editor_default_theme = isset( $options_default['editor_default_theme'] ) ? $options_default['editor_default_theme'] : $args['editor_default_theme'];;
        $editor_themes = $args['editor_themes'];

        $html = '<div class="default_editor_setting_container">';

        $html .= '<div id="editor_default_theme_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label"><strong><small>Default theme</strong></small></label>';
        $html .= '<select id="editor_default_theme" name="shortcode_load_default_options[editor_default_theme]" class="form-control">';

        foreach ($editor_themes as $editor_theme_name => $editor_theme_slug) {
            $selected = ($editor_default_theme == $editor_theme_slug) ? ' selected="selected"' : '';
            $html .= '<option value=' . $editor_theme_slug . $selected . '>' . $editor_theme_name . '</option>';
        }

        $html .= "</select>";
        $html .= '</div>'; // ./editor_default_theme_setting

        //Ace editor default font size
        $editor_default_font_size = isset( $options_default['editor_default_font_size'] ) ? $options_default['editor_default_font_size'] : $args['editor_default_font_size'];

        $html .= '<div id="editor_default_font_size_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label"><strong><small>Default font size</small></strong></label>';

        //Get all available font sizes and check which one is currently selected
        $editor_font_sizes = $args['editor_font_sizes'];

        $html .= '<select id="editor_default_font_size" name="shortcode_load_default_options[editor_default_font_size]" class="form-control">';
        foreach ($editor_font_sizes as $editor_font_size) {
            $selected = ($editor_default_font_size == $editor_font_size) ? ' selected="selected"' : '';
            $html .= '<option value=' . $editor_font_size . $selected . '>' . $editor_font_size . '</option>';
        }
        $html .= "</select>";
        $html .= '</div>'; // ./editor_default_font_size_setting

        //Ace editor default mode type
        $editor_default_mode_type = isset ( $options_default['editor_default_mode_type'] ) ? $options_default['editor_default_mode_type'] : $args['editor_default_mode_type'];

        $html .= '<div id="editor_default_mode_type_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label"><strong><small>Default type</small></strong></label>';

        //Get all available font sizes and check which one is currently selected
        $editor_mode_types = $args['editor_mode_types'];

        $html .= '<select id="editor_default_mode_type" name="shortcode_load_default_options[editor_default_mode_type]" class="form-control">';
        foreach ($editor_mode_types as $editor_mode_type_name => $editor_mode_type_slug) {
            $selected = ($editor_default_mode_type == $editor_mode_type_slug) ? ' selected="selected"' : '';
            $html .= '<option value=' . $editor_mode_type_slug . $selected . '>' . $editor_mode_type_name . '</option>';
        }
        $html .= "</select>";
        $html .= '</div>'; // ./editor_default_mode_type_setting

        //Ace editor default tab size
        $editor_default_tab_size = isset ( $options_default['editor_default_tab_size'] ) ? $options_default['editor_default_tab_size'] : $args['editor_default_tab_size'];
        $html .= '<div id="editor_default_tab_size_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label"><strong><small>Default tab size</small></strong></label>';
        $html .= '<input type="number" id="editor_default_tab_size" name="shortcode_load_default_options[editor_default_tab_size]" class="form-control" value="' . $editor_default_tab_size . '" />';
        $html .= '</div>'; // ./editor_default_tab_size_setting        

        //Ace editor default print margin
        $editor_default_print_margin = isset ( $options_default['editor_default_print_margin'] ) ? $options_default['editor_default_print_margin'] : $args['editor_default_print_margin'];
        var_dump($editor_default_print_margin);

        $html .= '<div id="editor_default_show_print_margin_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label"><strong><small>Show print margin</strong></small></label>';
        $html .= '<input type="checkbox" id="editor_default_print_margin" name="shortcode_load_default_options[editor_default_print_margin]" value="1"' . checked( $editor_default_print_margin, 1, false ) . '/>';
        $html .= '</div>'; // ./editor_default_show_print_margin_setting

        /*Ace editor default print margin column
            hide this section if print margin is disabled */
        $editor_default_print_margin_column = isset ( $options_default['editor_default_print_margin_column'] ) ? $options_default['editor_default_print_margin_column'] : $args['editor_default_print_margin_column'];

        $html .= '<div id="editor_default_print_margin_column_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label"><strong><small>Print margin column</small></strong></label>';
        $html .= '<input type="number" id="editor_default_print_margin_column" name="shortcode_load_default_options[editor_default_print_margin_column]" class="form-control" value="' . $editor_default_print_margin_column . '" />';
        $html .= '</div>'; // ./editor_default_print_margin_column_setting

        //Ace editor default show line numbers
        $editor_default_show_line_numbers = isset ( $options_default['editor_default_show_line_numbers'] ) ? $options_default['editor_default_show_line_numbers'] : $args['editor_default_show_line_numbers'];

        $html .= '<div id="editor_default_show_line_numbers_setting" class="default_options_sub_setting">';
        $html .= '<label class="control-label"><strong><small>Show editor line numbers</small></strong></label>';
        $html .= '<input type="checkbox" id="editor_default_show_line_numbers" name="shortcode_load_default_options[editor_default_show_line_numbers]" value="1"' . checked( $editor_default_show_line_numbers, 1, false ) . '/>';
        $html .= '</div>'; // ./editor_default_show_line_numbers_setting

        $html .= '</div>'; // ./default_editor_container

        echo $html;
    }

    /* Edit file tab callbacks */

    function shortcode_load_edit_file_options_callback() {
        $id = (isset($_GET['id'])) ? intval($_GET['id']) : false;
        $revision_override = (isset($_GET['revision'])) ? intval($_GET['revision']) : false;

        $options_default = get_option( 'shortcode_load_default_options' );
        $editor_default_mode_type = $options_default['editor_default_mode_type'];

        if($id) { //check if file id is supplied and load the content
            global $wpdb;
            $table_name = $wpdb->prefix . 'shortcode_load'; 

            $sql = "SELECT name,slug,type,revision,srcpath,minpath FROM ".$table_name." WHERE id = '".$id."' LIMIT 1";
            $result = $wpdb->get_results($sql, ARRAY_A)[0];

            extract($result); //turn array into named variables, see $sql SELECT query above for variable names

            //Check for revision override
            if($revision_override !== false) {
                if($revision_override <= $revision AND $revision_override > 0) {
                    $current_revision = $revision_override;
    
                    $srcname = basename($srcpath, $type);
                    $srcpath_base = dirname($srcpath) . '/';
                    $srcpath = $srcpath_base . $srcname . $current_revision . "." . $type;
                }
            } else {
                if($revision > 0) {
                    $current_revision = $revision;

                    $srcname = basename($srcpath, $type);
                    $srcpath_base = dirname($srcpath) . '/';
                    $srcpath = $srcpath_base . $srcname . $current_revision . "." . $type;
                } else {
                    $current_revision = "Source";
                }
            }

            $html = '<div id="edit_file_input_container">';

            $html .= '<label class="control-label">File: <em>' . $name . '</em></label>';

            /* Shortcode displayed in an input field */

            $html .= '<label class="control-label">Shortcode:</label>';

            $shortcode_display = 'shortcode_load id=' . $id;

            $html .='<input type="text" id="edit_file_shortcode_display" class="form-control" name="shortcode_load_edit_file_options[edit_file_shortcode_display]" readonly=readonly value="['.$shortcode_display.']"/>';

            /* Select revision dropdown */

            $html .= '<label class="control-label">Current revision:</label>';
            $html .= '<select id="edit_file_revisions_select" class="form-control" name="edit_file_revisions_select">';
            for ($i=$revision; $i >= 0; $i--) {
                $selected = ($current_revision==$i) ? ' selected="selected"' : '';
                $html .= '<option value='.$i.$selected.'>'.$i.'</option>';
            }
            $html .= "</select>";

            $html .= '</div>'; // ./edit_file_input_container

            echo $html;

            $content = $this->shortcode_load_get_file( $srcpath );

            if($content !== false) {
                //init editor with content
                $this->shortcode_load_editor_init($content, $type);
            } else {
                $this->shortcode_load_editor_init('File content was not found! Please report this error to the developer!', $editor_default_mode_type);
            }
        } else {
            //No file is selected, this is a new file

            $html = '<div id="edit_file_input_container">';

            //File name input
            $html .= '<label class="control-label">File name</label>';
            $html .= '<input type="text" id="new_file_name" class="form-control" name="shortcode_load_edit_file_options[new_file_name]" placeholder="Enter a file name" />';

            //File type select
            $html .= '<label class="control-label">File type</label>';
            $html .= '<select id="new_file_type" class="form-control" name="shortcode_load_edit_file_options[new_file_type]"><option selected=selected value="plain_text">File type</option><option value="javascript">JavaScript</option><option value="css">CSS</option></select>';

            //File upload
            $html .= '<div id="edit_file_file_upload_container">';

            $html .= '<div id="new_file_upload_reset_button">&#x2716;</div>';
            $html .= '<input type="text" id="new_file_upload_file_name" class="form-control" disabled="disabled" placeholder="Select File..." />';

            $html .= '<div id="new_file_upload_button" class="btn btn-primary"><span>Upload File</span>';
            $html .= '<input type="file" id="new_file_upload" name="shortcode_load_edit_file_options[new_file_upload]" accept="*.js|*.css|*.txt" />';
            $html .= '</div>'; // ./new_file_upload_button

            $html .= '</div>'; // ./edit_file_file_upload_container

            $html .= '</div>'; // ./edit_file_input_container

            echo $html;

            $this->shortcode_load_editor_init(false, $editor_default_mode_type);
        }
    }

    function shortcode_load_edit_file_source_options_callback() {
        $options_edit_file = get_option( 'shortcode_load_edit_file_options' );

        $current_id = isset($_GET['id']) ? ( intval ( $_GET['id'] ) ) : false;

        /*Create a textarea to temporarily hold the raw data from Ace editor
        this data will then be processed when the page is reloaded again (Save Changes button is pressed)
        The textarea will be continously updated with javascript
        */
        echo '<textarea id="edit_file_temporary_textarea" name="shortcode_load_edit_file_options[edit_file_temporary_textarea]">' . $options_edit_file[ 'edit_file_temporary_textarea' ] . '</textarea>';

        //We also need the id to refer to later, save this to a simple input field as well
        echo '<input type="text" id="edit_file_current_id" name="shortcode_load_edit_file_options[edit_file_current_id]" value="' . $current_id . '"/>';
        
    }

    /* Help tab callbacks */

    function shortcode_load_help_callback() {
        $html = '<div id="shortcode_load_help">';
        $html .= '<h4>Help and how-to</h4>';
        $html .= '<div id="shortcode_load_help_getting_started">';
        $html .= '</div>'; // ./shortcode_load_help_getting_started
        $html .= '</div>'; // ./shortcode_load_help

        echo $html;
    }

    function shortcode_load_help_documentation_callback() {
        $html = '<div id="shortcode_load_help_documentation">';
        $html .= '<h4>Documentation</h4>';
        $html .= '</div>'; // ./shortcode_load_help_documentation

        echo $html;
    }

    function shortcode_load_help_credits_callback() {
        $html = '<div id="shortcode_load_help_credits">';
        $html .= '<p>This plugin would not have been possible without the following projects.</p>';
        $html .= '<p>Much kudos to everyone in the world contributing to the open source software community!</p>';

        //Ace credits
        $html .= '<div class="shortcode_load_help_credits_section" id="shortcode_load_help_credits_ace">';
        $html .= '<label class="control-label">Ace</label>';
        $html .= '<p>Project URL: <a href="http://ace.c9.io/" target="_blank">Ace</a></p>';
        $html .= '<p>License: <a href="http://github.com/ajaxorg/ace/blob/master/LICENSE" target="_blank">BSD license</a></p>';
        $html .= '</div>'; // ./shortcode_load_help_credits_ace

        //Datatables credits
        $html .= '<div class="shortcode_load_help_credits_section" id="shortcode_load_help_credits_datatables">';
        $html .= '<label class="control-label">DataTables</label>';
        $html .= '<p>Project URL: <a href="http://www.datatables.net" target="_blank">DataTables</a></p>';
        $html .= '<p>License: <a href="http://www.datatables.net/license/mit" target="_blank">MIT License</a></p>';
        $html .= '</div>'; // ./shortcode_load_help_credits_datatables

        //Minify libs credits
        $html .= '<div class="shortcode_load_help_credits_section" id="shortcode_load_help_credits_minify">';
        $html .= '<label class="control-label">Minify</label>';
        $html .= '<p>Project URL: <a href="http://code.google.com/p/minify" target="_blank">Minify (Google Code)</a></p>';
        $html .= '<p>License: <a href="http://opensource.org/licenses/BSD-3-Clause" target="_blank">BSD License</a></p>';
        $html .= '</div>'; // ./shortcode_load_help_credits_minify

        //JShrink credits
        $html .= '<div class="shortcode_load_help_credits_section" id="shortcode_load_help_credits_jshrink">';
        $html .= '<label class="control-label">JShrink</label>';
        $html .= '<p>Project URL: <a href="http://github.com/tedious/JShrink" target="_blank">JShrink (GitHub)</a></p>';
        $html .= '<p>License: <a href="http://github.com/tedious/JShrink/blob/master/LICENSE" target="_blank">BSD License</a></p>';
        $html .= '</div>'; // ./shortcode_load_help_credits_jshrink

        //Bootstrap credits
        $html .= '<div class="shortcode_load_help_credits_section" id="shortcode_load_help_credits_bootstrap">';
        $html .= '<label class="control-label">Bootstrap</label>';
        $html .= '<p>Project URL: <a href="http://getbootstrap.com" target="_blank">Bootstrap</a></p>';
        $html .= '<p>License: <a href="http://github.com/twbs/bootstrap/blob/master/LICENSE" target="_blank">MIT License</a></p>';
        $html .= '</div>'; // ./shortcode_load_help_credits_bootstrap

        $html .= '</div>'; // ./shortcode_load_help_credits

        echo $html;
    }

    /*
    * Sanitization functions
    * Both for wordpress callbacks and for custom functions
    */

    function shortcode_load_filter_string($string) {
        $string = preg_replace("/[^a-zA-Z0-9]+/", "", $string);
        return $string;
    }

    function shortcode_load_edit_file_callback_sanitize($args) {
        //Get the default options
        $options_default = get_option( 'shortcode_load_default_options' );
        $minify = $options_default['default_minify_checkbox'];

        //Get the file name, content and type
        $file_name = ( $args[ 'new_file_name' ] ) ? $args[ 'new_file_name' ] : NULL;
        $file_content = ( $args[ 'edit_file_temporary_textarea' ] ) ? $args[ 'edit_file_temporary_textarea' ] : NULL;
        $file_type = ( $args[ 'new_file_type' ] ) ? $args[ 'new_file_type' ] : NULL;

        $id = ( $args['edit_file_current_id'] ) ? $args['edit_file_current_id'] : NULL;
       
        $file_datas = array();

        if( ! ( empty( $id ) ) ) { //file already exists, add revision
                $file_datas[] = $this->shortcode_load_add_file_revision(
                    array(
                        'content' => $file_content,
                        'id' => $id,
                        'minify' => $minify
                    )
                );
        } elseif( ! ( empty($_FILES) ) ) { //file(s) are being uploaded
            try {
                $file_content = file_get_contents( $_FILES['shortcode_load_edit_file_options']['tmp_name']['new_file_upload'] ); //get the raw content from the uploaded file

                $file_datas[] = $this->shortcode_load_save_to_database(
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
            }
        } elseif( ! (empty( $file_name ) ) ) { //new file, save it
            $file_datas[] = $this->shortcode_load_save_to_database(
                array(
                    'content' => $file_content,
                    'name' => $file_name,
                    'type' => $file_type,
                    'minify' => $minify
                )
            );
        }

        if( isset( $file_datas ) ) {
            $this->shortcode_load_add_settings_error($file_datas);
        } else {
            //TODO handle error
        }
    }

    /*
    * Used by sanitization functions to display messages to user via Settings API
    * $args = array( 'success' => bool, ['id' => id, 'name' => name] )
    */

    function shortcode_load_add_settings_error($array) {
        foreach ($array as $file_data) {
            if($file_data['success'] == true){
                $message_setting = 'file_update';
                $message_setting_slug = 'file_update';
                $message_type = 'updated';

                $message = $file_data['type'] . ' file <em>'.$file_data['name'].'</em> has been ' . $file_data['operation'] . ' successfully!';

                if($file_data['operation'] != 'updated') {
                    $message .= '<a href="?page=shortcode_load&tab=tab_edit&id='.$file_data['id'].'"> Click here to view/edit.</a>';
                }
                
            } elseif($file_data['success'] == false) {
                $message_setting = 'file_update';
                $message_setting_slug = 'file_update';
                $message_type = 'error';

                $message = $file_data['type'] . ' file could not be ' . $file_data['operation'] . '! <a href="?page=shortcode_load&tab_help#file_error_'. $file_data['error_id'] . '" target="_blank">Click here for more info.</a>';
            }
        }

        try {
            add_settings_error($message_setting, $message_setting_slug, $message, $message_type);
        } catch (Exception $e) {
            //var_dump($e);
        }
    }

    /*
    * Loads Ace editor settings with appropriate environment and content
    *
    * shortcode_load_editor_init( string , 'js|css|plain_text')
    */

    function shortcode_load_editor_init($content, $mode_type) {
        $options_default = get_option( 'shortcode_load_default_options' );

        //Ace default editor settings
        extract( $options_default);

        if($content) { //if an existing file is loaded, set the file's type as specified
            switch ($mode_type) {
                case 'js':
                    $editor_mode_type = 'javascript';
                    break;
                default:
                    $editor_mode_type = $mode_type;
                    break;
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
        }

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
                    tabSize:"<?php echo $editor_default_tab_size; ?>",
                    theme:"<?php echo $editor_default_theme; ?>",
                    showPrintMargin:"<?php echo $editor_default_print_margin; ?>",
                    printMarginColumn:"<?php echo $editor_default_print_margin_column; ?>",
                    showLineNumbers:"<?php echo $editor_default_show_line_numbers; ?>"
                };
            </script>
        <?php
    }

    function shortcode_load_load_file($name, $path, $is_script = false, $prefixSlug = false) {
        if( class_exists('ShortcodeLoad') ) {
            if($prefixSlug) {
                $name = ShortcodeLoad::slug . '-' . $name;
                $path = ShortcodeLoad::slug . '-' . $path;
            }

            ShortcodeLoad::load_file($name, $path, $is_script );
        }
    }

    function shortcode_load_options_page(  ) {

        if( isset( $_GET[ 'tab' ] ) ) {  
            $active_tab = $_GET[ 'tab' ];  
        } else {
            $active_tab = 'tab_overview';
        }

        ?>
        <div class="wrap">
            <div class="nav_tab_wrapper">
                <a href="?page=shortcode_load&amp;tab=tab_overview" class="nav_tab tab_overview <?php echo $active_class = ($active_tab == 'tab_overview') ? 'active_tab' : '' ?>">Overview</a>
                <a href="?page=shortcode_load&amp;tab=tab_default" class="nav_tab tab_default <?php echo $active_class = ($active_tab == 'tab_default') ? 'active_tab' : '' ?>">Default Options</a>
                <a href="?page=shortcode_load&amp;tab=tab_edit" class="nav_tab tab_edit <?php echo $active_class = ($active_tab == 'tab_edit') ? 'active_tab' : '' ?>">Edit file</a>
                <a href="?page=shortcode_load&amp;tab=tab_help" class="nav_tab tab_help <?php echo $active_class = ($active_tab == 'tab_help') ? 'active_tab' : '' ?>">Help</a>
            </div>

            <form action='options.php' method='post' enctype='multipart/form-data'>
                
                <h2>Shortcode Load</h2>
                
                <?php

                if($active_tab == 'tab_overview') {
                    //Libraries
                    $this->shortcode_load_load_file('datatables-style-bootstrap', 'admin-style/css/dataTables.bootstrap.css', false, true);
                    $this->shortcode_load_load_file('datatables-script', 'lib/datatables/media/js/jquery.dataTables.min.js', true, false);
                    $this->shortcode_load_load_file('datatables-script-bootstrap', 'admin-script/js/dataTables.bootstrap.js', true, true);

                    //Tab styles and scripts
                    $this->shortcode_load_load_file('tab_overview_js', 'admin-script/js/tab_overview.js', true, true);
                    $this->shortcode_load_load_file('tab_overview_css', 'admin-style/css/tab_overview.css', false, true);

                    //Tab sections and fields 
                    settings_fields( 'shortcode_load_overview' );
                    do_settings_sections( 'shortcode_load_overview' );

                } elseif($active_tab == 'tab_default') {
                    //Tab styles and scripts
                    $this->shortcode_load_load_file('tab_default_css', 'admin-style/css/tab_default.css', false, true);

                    //Tab sections and fields 
                    settings_fields( 'shortcode_load_default_options' );
                    do_settings_sections( 'shortcode_load_default_options' );

                    submit_button();
                } elseif($active_tab == 'tab_edit') {
                    //Libraries
                    $this->shortcode_load_load_file('ace-js', 'lib/ace/src-min-noconflict/ace.js', true, false);

                    //Tab styles and scripts
                    $this->shortcode_load_load_file('tab_edit_js', 'admin-script/js/tab_edit.js', true, true);
                    $this->shortcode_load_load_file('tab_edit_css', 'admin-style/css/tab_edit.css', false, true);

                    //Place a save button on top of page as well
                    submit_button('Save file', 'btn btn-lg btn-success');

                    //Tab sections and fields 
                    settings_fields( 'shortcode_load_edit_file_options' );
                    do_settings_sections( 'shortcode_load_edit_file_options' );

                    submit_button('Save file', 'btn btn-lg btn-success');
                } elseif($active_tab == 'tab_help') {
                    //Tab styles and scripts
                    $this->shortcode_load_load_file('tab_help_css', 'admin-style/css/tab_help.css', false, true);

                    //Tab sections and fields 
                    settings_fields( 'shortcode_load_help_section' );
                    do_settings_sections( 'shortcode_load_help_section' );
                }

                ?>
                
            </form>
        </div><!-- ./wrap -->
        <?php

    }
}

?>