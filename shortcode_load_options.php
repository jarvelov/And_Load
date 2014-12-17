<?php

Class ShortcodeLoad_Options {

	function __construct() {
		add_action( 'admin_menu', array($this, 'shortcode_load_add_admin_menu') );
		add_action( 'admin_init', array($this, 'shortcode_load_settings_init') );
		add_action( 'register_scripts_styles', array($this, 'shortcode_load_register_scripts_styles') );
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
			'shortcode_load_default_text',
			'Script Content Field',
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
			'shortcode_load_default_text',
			'Style Content Field',
			array($this, 'shortcode_load_new_style_textarea_callback'),
			'shortcode_load_new_style_options',
			'shortcode_load_new_style'
		);

		register_setting('shortcode_load_new_style_options', 'shortcode_load_new_style_options');

		do_action('register_scripts_styles');

	}

	/* Save new scripts/styles to database */

	function shortcode_load_register_scripts_styles() {
		$options_default = get_option( 'shortcode_load_default_options' );
		$options_scripts = get_option( 'shortcode_load_new_script_options' );
		$options_styles = get_option( 'shortcode_load_new_styles_options' );
		

		$script_content = ( $options_scripts[ 'new_script_textarea' ] ) ? $options_scripts[ 'new_script_textarea' ] : NULL;
		$style_content = ( $options_styles[ 'new_style_textarea' ] ) ? $options_scripts[ 'new_style_textarea' ] : NULL;

		$minify = ( isset( $options_default['default_minify_checkbox'] ) ) ? true : false;

		if($script_content) {
			$this->shortcode_load_save_to_database( array( 'content' => $script_content, 'type' => 'script', 'minify' => $minify ) );
		}

		if($style_content) {
			$this->shortcode_load_save_to_database( array( 'content' => $style_content, 'type' => 'style', 'minify' => $minify ) );
		}
	}

	function shortcode_load_save_to_database($args) {
		var_dump($args);
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

		$html = '<input type="checkbox" id="default_minify_checkbox" name="shortcode_load_default_options[default_minify_checkbox]" value="1"' . checked( 1, ( isset ( $options['default_minify_checkbox'] ) ? 1 : 0, false ) . '/>';
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
		echo '<p>Script Name *</p>';
	}	

	function shortcode_load_new_script_textarea_callback() {
		$options = get_option( 'shortcode_load_new_script_options' );
		echo '<p>Paste script into the textarea</p>';
		echo '<textarea id="new_script_textarea" name="shortcode_load_new_script_options[new_script_textarea]" rows="5" cols="50">' . $options[ 'new_script_textarea' ] . '</textarea>';
	}

	/* New style tab callbacks */

	function shortcode_load_new_style_options_callback() {
		echo '<p>New style callback</p>';
	}

	function shortcode_load_new_style_name_callback() {
		$options = get_option( 'shortcode_load_new_style_options' );
		echo '<p>Style Name *</p>';
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