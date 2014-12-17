<?php

Class ShortcodeLoad_Options {

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
				<a href="#" class="nav-tab">Default Options</a>
				<a href="#" class="nav-tab">New Script</a>
				<a href="#" class="nav-tab">New Style</a>
			</h2>

			<form action='options.php' method='post'>
				
				<h2>Shortcode Load</h2>
				
				<?php
				settings_fields( 'pluginPage' );
				do_settings_sections( 'pluginPage' );
				submit_button();
				?>
				
			</form>
		</div><!-- ./wrap -->
		<?php

	}
}

?>