<?php

Class And_Load_Options extends AndLoad {

    function __construct() {
        add_action( 'admin_init', array($this, 'and_load_settings_init') );
        add_action( 'admin_menu', array($this, 'and_load_add_admin_menu') );
        add_action( 'admin_head', array($this, 'and_load_header') );
        add_action( 'admin_enqueue_scripts', array( $this, 'ajax_script' ) );

    } //end __construct

    function and_load_add_admin_menu(  ) { 
        add_menu_page( 'And_Load', 'And_Load', 'manage_options', 'and_load', array($this, 'and_load_admin_page_overview') );
        add_submenu_page( 'and_load', 'Overview', 'Overview', 'manage_options', 'and_load', array($this, 'and_load_admin_page_overview') );
        add_submenu_page( 'and_load', 'Settings', 'Settings', 'manage_options', 'and_load_settings', array($this, 'and_load_admin_page_settings') );
        add_submenu_page( 'and_load', 'Editor', 'Editor', 'manage_options', 'and_load_editor', array($this, 'and_load_admin_page_editor') );
        add_submenu_page( 'and_load', 'Help', 'Help', 'manage_options', 'and_load_help', array($this, 'and_load_admin_page_help') );


    } // end and_load_add_admin_menu

    function and_load_settings_init()  {
        if ( ! current_user_can('manage_options') )
            return;        

        //Set up Plater PHP template
        $this->templates = new League\Plates\Engine( __DIR__ . '/templates');

        //Register default options
        $this->and_load_register_options();
    } // end and_load_settings_init

    /***************************
    * Default options *
    ***************************/

    /** and_load_get_options_default
    * Returns an array with the default options
    */

    function and_load_get_options_default() {
        return array(
            'default_minify' => array(
                'name' => 'Load Minified',
                'type' => 'checkbox',
                'default' => true,
                'description' => 'Automatically save a minified copy when saving/updating a file. If unchecked all files will load the unminified version of the file.'
            ),
            'default_jquery' => array(
                'name' => 'Load jQuery',
                'type' => 'checkbox',
                'default' => true,
                'description' => 'Automatically load jQuery as a dependency with javascript files (if loaded with wp_enqueue_script)'
            ),
            'overview_default_table_order_columns' => array(
                'name' => 'Default sort column',
                'type' => 'select',
                'default' => 0,
                'values' => array(
                    'id',
                    'type',
                    'name',
                    'revisions',
                    'last updated',
                    'created'
                ),
                'description' => 'Default column in overview table to sort by'
            ),
            'overview_default_table_sort' => array(
                'name' => 'Default sort order',
                'type' => 'select',
                'default' => 'desc',
                'values' => array(
                    'Ascending' => 'asc',
                    'Descending' => 'desc'
                ),
                'description' => 'Default sort order in overview table'
            ),
            'editor_theme' => array(
                'name' => 'Editor Theme',
                'type' => 'select',
                'default' => 'monokai',
                'values' => array(
                    'ambiance' => 'Ambiance',
                    'chaos' => 'Chaos',
                    'chrome' => 'Chrome',
                    'clouds' => 'Clouds',
                    'clouds_midnight' => 'Clouds Midnight',
                    'cobalt' => 'Cobalt',
                    'crimson_editor' => 'Crimson Editor',
                    'dawn' => 'Dawn',
                    'dreamweaver' => 'Dreamweaver',
                    'eclipse' => 'Eclipse',
                    'github' => 'GitHub',
                    'idle_fingers' => 'Idle Fingers',
                    'katzenmilch' => 'Katzenmilch',
                    'kr_theme' => 'Kr Theme',
                    'kuroir' => 'Kuroir',
                    'merbivore' => 'Merbivore',
                    'merbivore_soft' => 'Merbivore Soft',
                    'monokai' => 'Monokai (default)',
                    'mono_industrial' => 'Mono Industrial',
                    'pastel_on_dark' => 'Pastel on Dark',
                    'solarized_dark' => 'Solarized Dark',
                    'solarized_light' => 'Solarized Light',
                    'terminal' => 'Terminal',
                    'textmate' => 'Textmate',
                    'tomorrow' => 'Tomorrow',
                    'tomorrow_night' => 'Tomorrow Night',
                    'tomorrow_night_blue' => 'Tomorrow Night Blue',
                    'tomorrow_night_bright' => 'Tomorrow Night Bright',
                    'tomorrow_night_eighties' => 'Tomorrow Night Eighties',
                    'twilight' => 'Twilight',
                    'vibrant_ink' => 'Vibrant Ink',
                    'xcode' => 'Xcode'
                ),
                'description' => 'The default theme applied to the editor'
            ),
            'editor_font_size' => array(
                'name' => 'Editor Font Size',
                'type' => 'text',
                'default' => 12,
                'description' => 'The editor\'s font size in pixels'
            ),
            'editor_mode_type' => array(
                'name' => 'Editor Mode',
                'type' => 'select',
                'default' => 'plain_text',
                'values' => array(
                    'plain_text' => 'None',
                    'css' => 'CSS',
                    'javascript' => 'JavaScript'
                ),
                'description' => 'The Editor Mode applies selected language\'s syntax highlighting, linting etc. by default'
            ),
            'editor_tab_size' => array(
                'name' => 'Editor Tab Size',
                'type' => 'number',
                'default' => 4,
                'description' => 'The tab size used in the editor.'
            ),
            'editor_print_margin' => array(
                'name' => 'Editor Print Margin',
                'type' => 'number',
                'default' => 80,
                'description' => 'The editor margin in pixels'
            ),
            'editor_line_numbers' => array(
                'name' => 'Editor Line Numbers',
                'type' => 'checkbox',
                'default' => true,
                'description' => 'Toggle visibility of the editor\'s line numbers'
            )
        );
    } //end and_load_get_options_default

    public function and_load_register_options() {
        //Get default options
        $options_default = $this->and_load_get_options_default();

        //If options are not registered, set the default value
        $options_registered = get_option('and_load_default_options');
        
        foreach ($options_default as $option => $value) {
            if( $options_registered ) { //TODO: does not work as intended
                $options_default[$option]['value'] = $options_default[$option]['default'];
            }
        }

        //Register options
        update_option( 'and_load_default_options', $options_default );
    }

    /************
    * Callbacks *
    ************/

    /* Settings tab callbacks */
    function and_load_admin_page_settings() {
        wp_enqueue_style( 'tab_default_css', 'css/tab_default.css' );
        wp_enqueue_script( 'tab_default_js', 'js/tab_default.js' );

        $options_default = get_option( 'and_load_default_options' );
        echo $this->templates->render('tab_setting', array(
            'settings' => $options_default
        ) );
    }
    /* Help tab callbacks */

    public function and_load_admin_page_help() {
        //Tab styles and scripts
        wp_enqueue_style( 'tab_help_css', 'css/tab_help.css' );
        wp_enqueue_script( 'tab_help_js', 'js/tab_help.js' );

        echo $this->templates->render('tab_help');
    }

    public function and_load_admin_page_overview() {
        //Tab dependencies styles and scripts
        wp_enqueue_style( 'datatables-style-bootstrap', 'lib/datatables/css/dataTables.bootstrap.css', false );
        wp_enqueue_script( 'datatables-script', 'lib/datatables/js/jquery.dataTables.min.js', true );
        wp_enqueue_script( 'datatables-script-bootstrap', 'lib/datatables/js/dataTables.bootstrap.min.js', true );

        //Tab styles and scripts
        wp_enqueue_style( 'tab_overview_css', 'css/tab_overview.css', false );
        wp_enqueue_script( 'tab_overview_js', 'js/tab_overview.js', true );

        $options_default = get_option( 'and_load_default_options' );

        echo $this->templates->render('tab_overview', array(
            'settings' => $options_default,
            'files' => 0,
        ) );
    }

    public function and_load_admin_page_editor() {
        //Tab dependencies styles and scripts
        wp_enqueue_script( 'ace-js', 'lib/ace-builds/js/ace.js');
        wp_enqueue_script( 'bootbox-js', 'lib/bootbox/js/bootbox.js' );

        //Tab styles and scripts
        wp_enqueue_style( 'tab_edit_css', 'css/tab_edit.css' );
        wp_enqueue_script( 'tab_edit_js', 'js/tab_edit.js' );
        $id = ( isset( $_GET['id'] ) ) ? intval( $_GET['id'] ) : false;
        $revision = ( isset( $_GET['revision'] ) ) ? intval( $_GET['revision'] ) : false;

        $options_default = get_option( 'and_load_default_options' );

        if($id) {
            $file = $this->models->database->get_file($id, $revision);


            echo $this->templates->render('tab_editor', array(
                'file' => $file,
                'settings' => $options_default,
            ) );
        } else {
            echo $this->templates->render('tab_editor', array(
                'settings' => $options_default
            ) );
        }
    }

    public function and_load_header() {
        //Prefetch header for cdn
        echo '<link rel="dns-prefetch" href="//maxcdn.bootstrapcdn.com/">';

        //Enqueue styles and scripts used throughout plugin
        wp_enqueue_style('fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.2/css/font-awesome.min.css');
        wp_enqueue_style('bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_style('open-sans', '//fonts.googleapis.com/css?family=Open+Sans');
        wp_enqueue_script('bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');

        //General styles for all admin pages
        wp_enqueue_style( 'and_load_admin-style', 'css/admin.css' );


    }
}

?>
