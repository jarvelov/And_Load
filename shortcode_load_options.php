<?php

Class ShortcodeLoad_Options {

	function __construct() {
		add_action( 'admin_menu', array($this, 'shortcode_load_add_admin_menu') );
		add_action( 'admin_init', array($this, 'shortcode_load_settings_init') );
		add_filter( 'register_scripts_styles', array($this, 'shortcode_load_register_scripts_styles') );
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

		add_settings_field(
			'shortcode_load_overview_scripts',
			'Scripts',
			array($this, 'shortcode_load_overview_scripts_callback'),
			'shortcode_load_overview',
			'shortcode_load_overview'
		);

		add_settings_field(
			'shortcode_load_overview_styles',
			'Styles',
			array($this, 'shortcode_load_overview_styles_callback'),
			'shortcode_load_overview',
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
			'shortcode_load_default_text',
			'Default Settings Field',
			array($this, 'shortcode_load_default_text_callback'),
			'shortcode_load_default_options',
			'shortcode_load_default'
		);

		add_settings_field(
			'shortcode_load_automatically_minify',
			'Auto minify',
			array($this, 'shortcode_load_default_automatically_minify_callback'),
			'shortcode_load_default_options',
			'shortcode_load_default'
		);

		register_setting('shortcode_load_default_options', 'shortcode_load_default_options');

		/* New script section */

		add_settings_section( 
			'shortcode_load_new_script',
			'New Script Settings',
			array($this, 'shortcode_load_new_script_options_callback'),
			'shortcode_load_new_script_options'
		);

		add_settings_field(
			'shortcode_load_script_name',
			'Script Name *',
			array($this, 'shortcode_load_new_script_name_callback'),
			'shortcode_load_new_script_options',
			'shortcode_load_new_script'
		);		

		add_settings_field(
			'shortcode_load_default_text',
			'Script Content *',
			array($this, 'shortcode_load_new_script_textarea_callback'),
			'shortcode_load_new_script_options',
			'shortcode_load_new_script'
		);

		register_setting('shortcode_load_new_script_options', 'shortcode_load_new_script_options');

		/* New style section */

		add_settings_section( 
			'shortcode_load_new_style',
			'New Style Settings',
			array($this, 'shortcode_load_new_style_options_callback'),
			'shortcode_load_new_style_options'
		);

		add_settings_field(
			'shortcode_load_style_name',
			'Style Name *',
			array($this, 'shortcode_load_new_style_name_callback'),
			'shortcode_load_new_style_options',
			'shortcode_load_new_style'
		);			

		add_settings_field(
			'shortcode_load_default_text',
			'Style Content *',
			array($this, 'shortcode_load_new_style_textarea_callback'),
			'shortcode_load_new_style_options',
			'shortcode_load_new_style'
		);

		register_setting('shortcode_load_new_style_options', 'shortcode_load_new_style_options');

	}

	/* Database interactions */

	function shortcode_load_register_scripts_styles() {
		$options_default = get_option( 'shortcode_load_default_options' );
		$options_scripts = get_option( 'shortcode_load_new_script_options' );
		$options_styles = get_option( 'shortcode_load_new_styles_options' );
		
		$script_content = ( $options_scripts[ 'new_script_textarea' ] ) ? $options_scripts[ 'new_script_textarea' ] : NULL;
		$style_content = ( $options_styles[ 'new_style_textarea' ] ) ? $options_scripts[ 'new_style_textarea' ] : NULL;

		$minify = ( isset( $options_default['default_minify_checkbox'] ) ) ? true : false;

		if($script_content) {
			$name = $options_scripts[ 'new_script_name' ];
			$id = $this->shortcode_load_save_to_database( array( 'content' => $script_content, 'name' => $name, 'type' => 'js', 'minify' => $minify ) );
		}

		if($style_content) {
			$name = $options_styles[ 'new_style_name' ];
			$id = $this->shortcode_load_save_to_database( array( 'content' => $style_content, 'name' => $name, 'type' => 'css', 'minify' => $minify ) );
		}

		var_dump($id);

		if($id){
		?>
			<div class="updated"><p><strong><?php _e('File has been saved with id: '.$id, 'shortcode_load' ); ?></strong></p></div>
		<?php
		}
	}

	/* 
	* Save a new script or style to the database
	*/

	function shortcode_load_save_to_database($args) {
		try {
			$db_args = $this->shortcode_load_save_file($args);
		} catch (Exception $e) {
			//var_dump($e);
		}

		try {

			global $wpdb;
			$table_name = $wpdb->prefix . 'shortcode_load'; 

			$wpdb->insert( 
				$table_name, 
				array( 
					'name' => $db_args['name'],
					'type' => $db_args['type'],
					'srcpath' =>  $db_args['srcpath'],
					'minify' => $db_args['minify'],
					'minpath' => $db_args['minpath'],
					'revision' => 0, //TODO implement revisions
					'created_timestamp' => current_time('mysql', 1),
					'updated_timestamp' => current_time('mysql', 1),
				), 
				array( 
					'%s',
					'%s',
					'%s',
					'%d', 
					'%s',
					'%d',
					'%s',
					'%s'
				) 
			);
		} catch (Exception $e) {
			//var_dump($e);
		}

		$id = $wpdb->insert_id;

		if($id > 0) {
			return $id;	
		} else {
			return false;
		}
	}

	/*
	* Save a new script or style to a file in wordpress' uploads folder
	*/

	function shortcode_load_save_file($args) {
		$wp_uploads_path = wp_upload_dir();
		$uploads_dir = $wp_uploads_path['basedir'] . '/shortcode_load/';

		$type = $args['type'];
		$src_dir = $uploads_dir . $type . '/src/';

		$minify = $args['minify'];
		$content = $args['content'];
		
		$random5 = substr(md5(microtime()),rand(0,26),5); //generate 5 random numbers to ensure filename is unique
		$name = $args['name'] . $random5;

		$file_src = $src_dir . $name . '.' . $type;

		if (!is_dir($src_dir)) {
			wp_mkdir_p($src_dir);
		}

		if($minify == true) {
			$min_dir = $uploads_dir . $type . '/min/';

			if (!is_dir($min_dir)) {
				wp_mkdir_p($min_dir);
			}
		}

		if($type == 'js') {
			$file_args = $this->shortcode_load_save_file_js($file_src, $content, $minify);
		} elseif($type == 'css') {
			$file_args = $this->shortcode_load_save_file_css($file_src, $content, $minify);
		}

		$db_args = array('name' => $name, 'type' => $type, 'srcpath' => $file_args['srcpath'], 'minify' => $minify, 'minpath' => $file_args['minpath']);

		return $db_args;
	}

	/*
	* Save javascript content to path,
	* optionally save a minified version
	*/
	function shortcode_load_save_file_js($path, $content, $minify) {
		$file_args_array = array();

		try {
			file_put_contents($path, $content);
			$file_args_array['srcpath'] = $path;
		} catch (Exception $e) {
			//var_dump($e);
		}

		try {
			if($minify == true) {
				$minified_content = $this->shortcode_load_minify_js($content);
				$name = basename($path, '.js');
				$path_min = dirname(dirname($path)) . '/min/' . $name . '.min.js';
				$file_args_array['minpath'] = $path_min;

				file_put_contents($path_min, $minified_content);
			} else {
				$file_args_array['minpath'] = "";
			}
		} catch (Exception $e) {
			//var_dump($e);	
		}

		return $file_args_array;
	}

	/*
	* Save css content to path,
	* optionally save a minified version
	*/
	function shortcode_load_save_file_css($path, $content, $minify) {

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

	//not sure if i need these functions
	function shortcode_load_get_scripts() {
		$scripts_array = array();
		return $scripts_array;
	}

	//not sure if i need these functions
	function shortcode_load_get_styles() {
		$styles_array = array();
		return $styles_array;
	}

	/*
	* Callbacks
	**/

	/* Overview tab callbacks */

	function shortcode_load_overview_callback() {
		echo '<p>Overview of the currently registered scripts and styles</p>'; 
	}	

	function shortcode_load_overview_scripts_callback() {
		echo '<h2>Scripts:</h2>'; 
	}

	function shortcode_load_overview_styles_callback() {
		echo '<h2>Styles:</h2>'; 
	}

	/* Default tab callbacks */
	function shortcode_load_default_options_callback() {
		echo '<p>Default Options:</p>'; 
	}	

	function shortcode_load_default_text_callback() {
		echo '<p>This is some default text</p>'; 
	}


	function shortcode_load_default_automatically_minify_callback() {
		$options = get_option( 'shortcode_load_default_options' );

		$html = '<input type="checkbox" id="default_minify_checkbox" name="shortcode_load_default_options[default_minify_checkbox]" value="1"' . checked( 1, ( isset ( $options['default_minify_checkbox'] ) ? 1 : 0), false ) . '/>';
		$html .= '<label for="default_minify_checkbox"><small>Automatically minify styles and scripts?</small></label>';
		echo $html;
}	

	/* New script tab callbacks */

	function shortcode_load_new_script_options_callback() {
		$options = get_option( 'shortcode_load_new_script_options' );
		echo '<p>New script</p>';
	}

	function shortcode_load_new_script_name_callback() {
		$options = get_option( 'shortcode_load_new_script_options' );
		echo '<input type="text" id="new_script_name" name="shortcode_load_new_script_options[new_script_name]" value="' . $options[ 'new_script_name' ] . '"/>';
	}	

	function shortcode_load_new_script_textarea_callback() {
		$options = get_option( 'shortcode_load_new_script_options' );
		echo '<p>Paste script into the textarea</p>';
		echo '<textarea id="new_script_textarea" name="shortcode_load_new_script_options[new_script_textarea]" rows="5" cols="50">' . $options[ 'new_script_textarea' ] . '</textarea>';
	}

	/* New style tab callbacks */

	function shortcode_load_new_style_options_callback() {
		echo '<p>New style</p>';
	}

	function shortcode_load_new_style_name_callback() {
		$options = get_option( 'shortcode_load_new_style_options' );
		echo '<input type="text" id="new_style_name" name="shortcode_load_new_style_options[new_style_name]" value="' . $options[ 'new_style_name' ] . '"/>';
	}		

	function shortcode_load_new_style_textarea_callback() {
		$options = get_option( 'shortcode_load_new_style_options' );
		echo '<p>Paste style into the textarea</p>';
		echo '<textarea id="new_style_textarea" name="shortcode_load_new_style_options[new_style_textarea]" rows="5" cols="50">' . $options[ 'new_style_textarea' ] . '</textarea>';
	}

	function shortcode_load_options_page(  ) { 
		if( isset( $_GET[ 'tab' ] ) ) {  
			$active_tab = $_GET[ 'tab' ];  
		} else {
			$active_tab = 'tab_one';
		}

		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<a href="?page=shortcode_load&amp;tab=tab_one" class="nav-tab">Overview</a>
				<a href="?page=shortcode_load&amp;tab=tab_two" class="nav-tab">Default Options</a>
				<a href="?page=shortcode_load&amp;tab=tab_three" class="nav-tab">New Script</a>
				<a href="?page=shortcode_load&amp;tab=tab_four" class="nav-tab">New Style</a>
			</h2>

			<?php do_action('register_scripts_styles'); ?>

			<form action='options.php' method='post'>
				
				<h2>Shortcode Load</h2>
				
				<?php

				if($active_tab == 'tab_one') {
					settings_fields( 'shortcode_load_overview' );
					do_settings_sections( 'shortcode_load_overview' );
				} elseif($active_tab == 'tab_two') {
					settings_fields( 'shortcode_load_default_options' );
					do_settings_sections( 'shortcode_load_default_options' );
				} elseif($active_tab == 'tab_three') {
					settings_fields( 'shortcode_load_new_script_options' );
					do_settings_sections( 'shortcode_load_new_script_options' );
				} elseif($active_tab == 'tab_four') {
					settings_fields( 'shortcode_load_new_style_options' );
					do_settings_sections( 'shortcode_load_new_style_options' );
				}

				submit_button();
				?>
				
			</form>
		</div><!-- ./wrap -->
		<?php

	}
}

?>