<?php

Class ShortcodeLoad_Options extends ShortcodeLoad {

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
			'shortcode_load_overview_styles',
			'Overview',
			array($this, 'shortcode_load_overview_scripts_styles_callback'),
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
				'default_editor_theme' => 'monokai',
				'editor_themes' => array(
					'ambiance',
					'chaos',
					'chrome',
					'clouds',
					'clouds_midnight',
					'cobalt',
					'crimson_editor',
					'dawn',
					'dreamweaver',
					'eclipse',
					'github',
					'idle_fingers',
					'katzenmilch',
					'kr_theme',
					'kuroir',
					'merbivore',
					'merbivore_soft',
					'monokai',
					'mono_industrial',
					'pastel_on_dark',
					'solarized_dark',
					'solarized_light',
					'terminal',
					'textmate',
					'tomorrow',
					'tomorrow_night',
					'tomorrow_night_blue',
					'tomorrow_night_bright',
					'tomorrow_night_eighties',
					'twilight',
					'vibrant_ink',
					'xcode'
				)
			)

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

		register_setting('shortcode_load_new_script_options', 'shortcode_load_new_script_options', array($this, 'shortcode_load_new_script_callback_sanitize') );

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

		register_setting('shortcode_load_new_style_options', 'shortcode_load_new_style_options', array($this, 'shortcode_load_new_style_callback_sanitize'));

		/* Edit file section */

		add_settings_section( 
			'shortcode_load_edit_file',
			'Edit file',
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

	}

	/*
	* Register new scripts and styles
	*/

	function shortcode_load_register_scripts_styles() {
		//cleared out to sanitize functions 2015-03-05
	}

	/* 
	* Save a new script or style to the database
	*/
	function shortcode_load_save_to_database($args) {
		try {
			$db_args = $this->shortcode_load_save_file($args);
		} catch (Exception $e) {
			$error_id = 0; //0 = could not write file to local path specified. Check permissions.
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
					'revision' => 0,
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

			$id = $wpdb->insert_id;
		} catch (Exception $e) {
			//var_dump($e);
			$error_id = 1;
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
					'revision' => $revision,	// int
					'updated_timestamp' => current_time('mysql', 1),
				), 
				array( 
					'ID' => $id
				), 
				array(
					'%d',
					'%s'
				),
				array('%d')
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

		$type = $args['type'];

		$src_dir = $uploads_dir . $type . '/src/';
		$min_dir = $uploads_dir . $type . '/min/';
		
		$random5 = substr(md5(microtime()),rand(0,26),5); //generate 5 random characters to ensure filename is unique
		$name = $org_name . '.' . $random5;

		$name = $this->shortcode_load_filter_string($name); //filter out any characters we don't want in path

		$file_src = $src_dir . $name . '.' . $type;

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
		}

		$db_args = array('name' => $org_name, 'type' => $type, 'srcpath' => $file_args['srcpath'], 'minify' => $minify, 'minpath' => $file_args['minpath']);

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
				$name = basename($path, '.js');
				$path_min = dirname(dirname($path)) . '/min/' . $name . '.min.js';
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
				$name = basename($path, '.css');
				$path_min = dirname(dirname($path)) . '/min/' . $name . '.min.css';
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

			$sql = "SELECT name,type,revision,srcpath,minify FROM ".$table_name." WHERE id = ".(int)$id." LIMIT 1";
			$result = $wpdb->get_results($sql, ARRAY_A)[0];
		} catch (Exception $e) {
			//var_dump($e);
			$error_id = 2; //2 = database lookup error, does entry with $id exist?
		}

		extract($result); //extract array to named variables, see $sql SELECT query above

		$new_revision = ( intval($revision) + 1);

		$srcname = basename($srcpath, $type);
		$unique_suffix = str_replace($name, "", $srcname);
		$new_name = $name . $unique_suffix . $new_revision . "." . $type;

		$file_src_base = dirname($srcpath) . '/';
		$file_src = $file_src_base . $new_name;

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

		$sql = "SELECT id,name,revision,updated_timestamp,created_timestamp FROM ".$table_name." WHERE type = 'js' ORDER BY created_timestamp DESC";
		$result = $wpdb->get_results($sql, ARRAY_A);

		return $result;
	}

	/*
	* Return all entries of type 'css' in database
	*/
	function shortcode_load_get_styles() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shortcode_load'; 

		$sql = "SELECT id,name,revision,updated_timestamp,created_timestamp FROM ".$table_name." WHERE type = 'css' ORDER BY created_timestamp DESC";
		$result = $wpdb->get_results($sql, ARRAY_A);

		return $result;
	}

	/*
	* Return all saved entries regardless of type in database
	*/
	function shortcode_load_get_scripts_styles() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shortcode_load'; 

		$sql = "SELECT id,name,type,revision,updated_timestamp,created_timestamp FROM ".$table_name." ORDER BY created_timestamp DESC";
		$result = $wpdb->get_results($sql, ARRAY_A);

		return $result;
	}	

	/*
	* Returns a file's content.
	* If the file isn't found bool(false) is returned.
	* @path 	Path to file
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
	* Reset saved option
	* 
	* Called when a new script or style
	* has been successfully saved to
	* the database.
	*/

	function shortcode_load_reset_options() {
		$options_scripts = get_option( 'shortcode_load_new_script_options' );
		$options_styles = get_option( 'shortcode_load_new_style_options[' );

		$scripts_options_array = array();
		foreach ($options_scripts as $key => $value) {
			$scripts_options_array[$key] = "";
		}

		update_option('shortcode_load_new_script_options', $scripts_options_array);

		$style_options_array = array();
		foreach ($options_styles as $key => $value) {
			$style_options_array[$key] = "";
		}

		update_option('shortcode_load_new_style_options', $style_options_array);
	}

	/*
	* Callbacks
	**/

	/* Overview tab callbacks */

	function shortcode_load_overview_callback() {
		echo '<p>Overview of the currently registered scripts and styles</p>'; 

		echo '<input type="text" id="overview_filter" name="shortcode_load_overview[overview_filter]" placeholder="Type to filter..." />';
	}	

	function shortcode_load_overview_scripts_styles_callback() {
		$files = $this->shortcode_load_get_scripts_styles();

		$html = '<div class="shortcode-load-file-block-container">';

		if(sizeof($files) > 0) {
			foreach ($files as $file) {
				$file_id = $file['id'];
				$file_name = $file['name'];
				$file_type = $file['type'];
				$file_updated = $file['updated_timestamp'];
				$file_revision = $file['revision'];

				$html .= '<div id="shortcode-load-id-'.$file_id.'" class="shortcode-load-file-'.$file_type.' shortcode-load-file-block">';
				$html .= '<p class="shortcode-load-file-block-tag">'.strtoupper($file_type).'</p>';
				$html .= '<p class="shortcode-load-file-block-revision">'.$file_revision.'</p>';
				$html .= '<span><a href="?page=shortcode_load&amp;tab=tab_edit&amp;id='.$file_id.'" title="Updated: '.$file_updated.'">'.$file_name.'</a></span>';
				$html .= '</div>';
			}
		} else {
			$html .= '<h2>No scripts or styles created yet. Click the "New Style" or "New Script" tab above to get started!</h2>';
		}

		$html .= '</div>';

		echo $html;
	}


	/* Default tab callbacks */
	function shortcode_load_default_options_callback() {
		echo '<p>Default options to configure editor settings and minify behaviour.</p>'; 
	}

	function shortcode_load_default_automatically_minify_callback($args) {
		$options_default = get_option( 'shortcode_load_default_options' );
		$default_value = $args['default'];

		$html = '<input type="checkbox" id="default_minify_checkbox" name="shortcode_load_default_options[default_minify_checkbox]" value="1"' . checked( 1, ( isset ( $options_default['default_minify_checkbox'] ) ? $options_default['default_minify_checkbox'] : $default_value), false ) . '/>';
		$html .= '<label for="default_minify_checkbox"><small>Automatically minify styles and scripts? --BROKEN--</small></label>';
		echo $html;
	}

	function shortcode_load_default_editor_settings_callback($args) {
		/* TODO insert a dropdown for Ace editor default theme and other options */
		$options_default = get_option( 'shortcode_load_default_options' );

		$default_editor_theme = isset($options_default['default_editor_theme']) ? $options_default['default_editor_theme'] : $args['default_editor_theme'];;

		$editor_themes = $args['editor_themes'];

		$html = '<p class="margin-bottom-5"><strong><small>Theme</p></strong></small>';
		$html .= '<select id="default_editor_theme" name="shortcode_load_default_options[default_editor_theme]">';

		for ($i=0; $i < sizeof($editor_themes); $i++) { 
			$editor_theme = $editor_themes[$i];
			$selected = ($default_editor_theme == $editor_theme) ? ' selected="selected"' : '';
			$html .= '<option value='.$editor_theme.$selected.'>'.$editor_theme.'</option>';
		}

		$html .= "</select>";

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

	/* Edit file tab callbacks */

	function shortcode_load_edit_file_options_callback() {
		//TODO create a select dropdown in this function

		$id = (isset($_GET['id'])) ? intval($_GET['id']) : false;
		$revision_override = (isset($_GET['revision'])) ? intval($_GET['revision']) : false;

		if($id) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'shortcode_load'; 

			$sql = "SELECT name,type,revision,srcpath,minpath FROM ".$table_name." WHERE id = '".$id."' LIMIT 1";
			$result = $wpdb->get_results($sql, ARRAY_A)[0];

			extract($result); //turn array into named variables, see $sql SELECT query

			//Check for revision override ad 
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

			$html = '<p><strong>File: </strong>' . $name . '</p>';

			/* Select revision dropdown */

			$html .= '<p><strong>Revision: </strong>';
			$html .= '<select id="edit_file_revisions_select" name="edit_file_revisions_select">';
			for ($i=$revision; $i >= 0; $i--) {
				$selected = ($current_revision==$i) ? ' selected="selected"' : '';
				$html .= '<option value='.$i.$selected.'>'.$i.'</option>';
			}
			$html .= "</select></p>";

			/* shortcode displayed in input field */

			$html .= '<p><strong>Shortcode: </strong>';

			$shortcode_display = 'shortcode_load id=' . $id;

			$html .= '<div id="shortcode-load-shortcode-display-container">';
			$html .='<input type="text" id="edit_file_shortcode_display" name="shortcode_load_edit_file_options[edit_file_shortcode_display]" readonly=readonly value="'.$shortcode_display.'"/>'
			$html .= '</div>';
			$html .= '</p>';

			$content = $this->shortcode_load_get_file( $srcpath );

			if($content) {
				//init editor with content
				$this->shortcode_load_editor_init($content, $type);
			} else {
				//TODO handle error
			}
		} else {
			//TODO write out error message about no file selected
		}
	}

	function shortcode_load_edit_file_source_options_callback() {
		$options_edit_file = get_option( 'shortcode_load_edit_file_options' );

		$current_id = intval ( $_GET['id'] ); //ensure integer value only

		/*Create a textarea to temporarily hold the raw data from Ace editor
		this data will then be processed when the page is reloaded again (Save Changes button is pressed)
		The textarea will be continously updated with javascript
		*/
		echo '<textarea id="edit_file_temporary_textarea" class="hidden-display" name="shortcode_load_edit_file_options[edit_file_temporary_textarea]">' . $options_edit_file[ 'edit_file_temporary_textarea' ] . '</textarea>';

		//We also need the id to refer to later, save this to a simple input field as well
		echo '<input type="text" id="edit_file_current_id" class="hidden-display" name="shortcode_load_edit_file_options[edit_file_current_id]" value="' . $current_id . '"/>';
		
	}

	/*
	* Sanitization functions
	* Both for wordpress callbacks and for custom functions
	*/

	function shortcode_load_filter_string($string) {
		$string = preg_replace("/[^a-zA-Z0-9]+/", "", $string);
		return $string;
	}

	function shortcode_load_new_style_callback_sanitize($args) {
		$options_default = get_option( 'shortcode_load_default_options' );
		$style_content = ( $args[ 'new_style_textarea' ] ) ? $args[ 'new_style_textarea' ] : NULL;

		$minify = ( isset( $options_default['default_minify_checkbox'] ) ) ? true : false;

		if(!empty($style_content)) {
			$name = $args[ 'new_style_name' ];
			$file_datas[] = $this->shortcode_load_save_to_database(
				array(
					'content' => $style_content,
					'name' => $name,
					'type' => 'css',
					'minify' => $minify
				)
			);

			$this->shortcode_load_add_settings_error($file_datas);
		}
	}

	function shortcode_load_new_script_callback_sanitize($args) {
		$options_default = get_option( 'shortcode_load_default_options' );
		$script_content = ( $args[ 'new_script_textarea' ] ) ? $args[ 'new_script_textarea' ] : NULL;

		$file_datas = array();

		$minify = ( isset( $options_default['default_minify_checkbox'] ) ) ? true : false;

		if(!empty($script_content)) {
			$name = $args[ 'new_script_name' ];
			$file_datas[] = $this->shortcode_load_save_to_database(
				array(
					'content' => $script_content,
					'name' => $name,
					'type' => 'js',
					'minify' => $minify
				)
			);

			$this->shortcode_load_add_settings_error($file_datas);
		}
	}

	function shortcode_load_edit_file_callback_sanitize($args) {
		$options_default = get_option( 'shortcode_load_default_options' );		
		$edit_file_content = ( $args[ 'edit_file_temporary_textarea' ] ) ? $args[ 'edit_file_temporary_textarea' ] : NULL;
		$minify = ( isset( $options_default['default_minify_checkbox'] ) ) ? true : false;

		$file_datas = array();

		if(!empty($edit_file_content)) {
			$id = $args['edit_file_current_id'];
			$file_datas[] = $this->shortcode_load_add_file_revision(
				array(
					'content' => $edit_file_content,
					'id' => $id,
					'minify' => $minify
				)
			);

			$this->shortcode_load_add_settings_error($file_datas);
		}
	}

	/*
	* Used by sanitization functions to display messages to user via Settings API
	* $args = array( 'success' => bool, ['id' => id, 'name' => name] )
	*/

	function shortcode_load_add_settings_error($array) {
		var_dump($array);
		foreach ($array as $file_data) {
			if($file_data['success'] == true){
				$this->shortcode_load_reset_options(); //clear all the data in temporary fields

				$message_setting = 'file_update';
				$message_setting_slug = 'file_update';
				$message = $file_data['type'] . ' file <em>'.$file_data['name'].'</em> has been ' . $file_data['operation'] . ' successfully! <a href="?page=shortcode_load&tab=tab_edit&id='.$file_data['id'].'">Click here to view/edit.</a>';
				$message_type = 'updated';
				
			} elseif($file_data['success'] == false) {
				$message_setting = 'file_update';
				$message_setting_slug = 'file_update';
				$message = $file_data['type'] . ' file could not be ' . $file_data['operation'] . '! <a href="?page=shortcode_load&tab_help#file_error_'. $file_data['error_id'] . '" target="_blank">Click here for more info.</a>';
				$message_type = 'error';
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
	* shortcode_load_editor_init( string , 'js|css')
	*/

	function shortcode_load_editor_init($content, $type) {
		$options_default = get_option( 'shortcode_load_default_options' );

		//Ace editor settings
		$editor_theme = $options_default['default_editor_theme'];

		if($type == 'js') {
			$editor_mode = 'javascript';
		} elseif ($type == 'css') { 
			$editor_mode = 'css';
		}

		//Build Ace editor
		$container = '<div class="editor-container">';
		$editor = '<div id="editor">'.$content.'</div>';
		$container .= $editor . '</div>';

		echo $container;

		?>
			<script>
				var editor = ace.edit("editor");
				editor.setTheme("ace/theme/<?php echo $editor_theme; ?>");
				editor.getSession().setMode("ace/mode/<?php echo $editor_mode; ?>");
			</script>
		<?php

		if(class_exists(ShortcodeLoad)) {
			ShortcodeLoad::load_file( ShortcodeLoad::slug . '-ace-editor-js', ShortcodeLoad::slug . '-script/js/ace_edit.js', true );
		}
	}

	function shortcode_load_options_page(  ) {

		do_action('register_scripts_styles');

		if( isset( $_GET[ 'tab' ] ) ) {  
			$active_tab = $_GET[ 'tab' ];  
		} else {
			$active_tab = 'tab_overview';
		}

		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<a href="?page=shortcode_load&amp;tab=tab_overview" class="nav-tab <?php echo $active_class = ($active_tab == 'tab_overview') ? 'active-tab' : '' ?>">Overview</a>
				<a href="?page=shortcode_load&amp;tab=tab_default" class="nav-tab <?php echo $active_class = ($active_tab == 'tab_default') ? 'active-tab' : '' ?>">Default Options</a>
				<a href="?page=shortcode_load&amp;tab=tab_new_script" class="nav-tab <?php echo $active_class = ($active_tab == 'tab_new_script') ? 'active-tab' : '' ?>">New Script</a>
				<a href="?page=shortcode_load&amp;tab=tab_new_style" class="nav-tab <?php echo $active_class = ($active_tab == 'tab_new_style') ? 'active-tab' : '' ?>">New Style</a>
				<a href="?page=shortcode_load&amp;tab=tab_edit" class="nav-tab <?php echo $active_class = ($active_tab == 'tab_edit') ? 'active-tab' : '' ?>">Edit file</a>
				<a href="?page=shortcode_load&amp;tab=tab_help" class="nav-tab <?php echo $active_class = ($active_tab == 'tab_help') ? 'active-tab' : '' ?>">Help</a>
			</h2>

			<form action='options.php' method='post'>
				
				<h2>Shortcode Load</h2>
				
				<?php

				if($active_tab == 'tab_overview') {
					settings_fields( 'shortcode_load_overview' );
					do_settings_sections( 'shortcode_load_overview' );
				} elseif($active_tab == 'tab_default') {
					settings_fields( 'shortcode_load_default_options' );
					do_settings_sections( 'shortcode_load_default_options' );
				} elseif($active_tab == 'tab_new_script') {
					settings_fields( 'shortcode_load_new_script_options' );
					do_settings_sections( 'shortcode_load_new_script_options' );
				} elseif($active_tab == 'tab_new_style') {
					settings_fields( 'shortcode_load_new_style_options' );
					do_settings_sections( 'shortcode_load_new_style_options' );
				} elseif($active_tab == 'tab_edit') {
					settings_fields( 'shortcode_load_edit_file_options' );
					do_settings_sections( 'shortcode_load_edit_file_options' );	
				}

				submit_button();
				?>
				
			</form>
		</div><!-- ./wrap -->
		<?php

	}
}

?>