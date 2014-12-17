<?php

Class ShortcodeLoad_Options {


	function __construct() {
		add_action( 'admin_menu', array($this, 'sl_add_admin_menu') );
		add_action( 'admin_init', array($this, 'sl_settings_init') );
	}

	function sl_add_admin_menu(  ) { 

		add_options_page( 'Shortcode Load', 'Shortcode Load', 'manage_options', 'shortcode_load', 'shortcode_load_options_page' );

	}

	function sl_settings_init(  ) {

		if ( ! current_user_can('update_plugins') )
			return;

		register_setting( 'pluginPage', 'sl_settings' );

		add_settings_section(
			array($this, 'sl_pluginPage_section'), 
			__( 'Your section description', 'sl' ), 
			'sl_settings_section_callback', 
			'pluginPage'
		);

		add_settings_field( 
			array($this,'sl_text_field_0'), 
			__( 'Settings field description', 'sl' ), 
			'sl_text_field_0_render', 
			'pluginPage', 
			'sl_pluginPage_section' 
		);

		add_settings_field( 
			array($this,'sl_checkbox_field_1'), 
			__( 'Settings field description', 'sl' ), 
			'sl_checkbox_field_1_render', 
			'pluginPage', 
			'sl_pluginPage_section' 
		);

		add_settings_field( 
			array($this,'sl_radio_field_2'), 
			__( 'Settings field description', 'sl' ), 
			'sl_radio_field_2_render', 
			'pluginPage', 
			'sl_pluginPage_section' 
		);

		add_settings_field( 
			array($this,'sl_textarea_field_3'), 
			__( 'Settings field description', 'sl' ), 
			'sl_textarea_field_3_render', 
			'pluginPage', 
			'sl_pluginPage_section' 
		);

		add_settings_field( 
			array($this,'sl_select_field_4'), 
			__( 'Settings field description', 'sl' ), 
			'sl_select_field_4_render', 
			'pluginPage', 
			'sl_pluginPage_section' 
		);


	}


	function sl_text_field_0_render(  ) { 

		$options = get_option( 'sl_settings' );
		?>
		<input type='text' name='sl_settings[sl_text_field_0]' value='<?php echo $options['sl_text_field_0']; ?>'>
		<?php

	}


	function sl_checkbox_field_1_render(  ) { 

		$options = get_option( 'sl_settings' );
		?>
		<input type='checkbox' name='sl_settings[sl_checkbox_field_1]' <?php checked( $options['sl_checkbox_field_1'], 1 ); ?> value='1'>
		<?php

	}


	function sl_radio_field_2_render(  ) { 

		$options = get_option( 'sl_settings' );
		?>
		<input type='radio' name='sl_settings[sl_radio_field_2]' <?php checked( $options['sl_radio_field_2'], 1 ); ?> value='1'>
		<?php

	}


	function sl_textarea_field_3_render(  ) { 

		$options = get_option( 'sl_settings' );
		?>
		<textarea cols='40' rows='5' name='sl_settings[sl_textarea_field_3]'> 
			<?php echo $options['sl_textarea_field_3']; ?>
		</textarea>
		<?php

	}


	function sl_select_field_4_render(  ) { 

		$options = get_option( 'sl_settings' );
		?>
		<select name='sl_settings[sl_select_field_4]'>
			<option value='1' <?php selected( $options['sl_select_field_4'], 1 ); ?>>Option 1</option>
			<option value='2' <?php selected( $options['sl_select_field_4'], 2 ); ?>>Option 2</option>
		</select>

	<?php

	}


	function sl_settings_section_callback(  ) { 

		echo __( 'This section description', 'sl' );

	}


	function sl_options_page(  ) { 

		?>
		<form action='options.php' method='post'>
			
			<h2>Shortcode Load</h2>
			
			<?php
			settings_fields( 'pluginPage' );
			do_settings_sections( 'pluginPage' );
			submit_button();
			?>
			
		</form>
		<?php

	}
}

?>