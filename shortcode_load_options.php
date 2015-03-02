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

		register_setting('shortcode_load_edit_file_options', 'shortcode_load_edit_file_options');

	}

	/*
	* Register new scripts and styles
	*/

	function shortcode_load_register_scripts_styles() {

		//Load bootstrap styles and scripts
		/*
		ShortcodeLoad::load_file( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js', true );
		ShortcodeLoad::load_file( 'bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css' );
		*/

		$options_default = get_option( 'shortcode_load_default_options' );
		$options_scripts = get_option( 'shortcode_load_new_script_options' );
		$options_styles = get_option( 'shortcode_load_new_style_options' );
		$options_edit_file = get_option( 'shortcode_load_edit_file_options' );
		
		$script_content = ( $options_scripts[ 'new_script_textarea' ] ) ? $options_scripts[ 'new_script_textarea' ] : NULL;
		$style_content = ( $options_styles[ 'new_style_textarea' ] ) ? $options_styles[ 'new_style_textarea' ] : NULL;
		$edit_file_content = ( $options_edit_file[ 'edit_file_temporary_textarea' ] ) ? $options_edit_file[ 'edit_file_temporary_textarea' ] : NULL;
		
		$minify = ( isset( $options_default['default_minify_checkbox'] ) ) ? true : false;

		$file_datas = array();

		if(!empty($script_content)) {
			$name = $options_scripts[ 'new_script_name' ];
			$file_datas[] = $this->shortcode_load_save_to_database( array( 'content' => $script_content, 'name' => $name, 'type' => 'js', 'minify' => $minify ) );
		}

		if(!empty($style_content)) {
			$name = $options_styles[ 'new_style_name' ];
			$file_datas[] = $this->shortcode_load_save_to_database( array( 'content' => $style_content, 'name' => $name, 'type' => 'css', 'minify' => $minify ) );
		}

		if(!empty($edit_file_content)) {
			$id = $options_edit_file['edit_file_current_id'];
			$test_file_data = $this->shortcode_load_add_file_revision( array( 'content' => $edit_file_content, 'id' => $id, 'minify' => $minify ) );
		}

		foreach ($file_datas as $file_data) {
			if($file_data['success'] == true){
				$this->shortcode_load_reset_options();
			?>
				<div class="updated"><p><strong><?php _e($file_data['type'] . ' file <em>'.$file_data['name'].'</em> has been saved successfully! <a href="?page=shortcode_load&tab=tab_edit&id='.$file_data['id'].'">Click here to view/edit.</a>', 'shortcode_load' ); ?></strong></p></div>
			<?php
			} elseif($file_data['success'] == false) {
			?>
				<div class="error"><p><strong><?php _e($file_data['type'] . ' file could not be saved! <a href="?page=shortcode_load&tab_help#file_error" target="_blank">Click here for more info.</a>', 'shortcode_load' ); ?></strong></p></div>
			<?php
			}
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

			$id = $wpdb->insert_id;
		} catch (Exception $e) {
			//var_dump($e);
		}

		if($id > 0) {
			$name = $db_args['name'] . '.' . $db_args['type'];
			$type = ($db_args['type'] == 'js') ? 'Script' : 'Style';
			$return_args = array('success' => true, 'id' => $id, 'name' => $name, 'type' => $type);
		} else {
			$return_args = array('success' => false);
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

		if($file_args_array['success'] != true) {
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

		if($file_args_array['success'] != true) {
			$file_args_array['success'] = true;
		}

		return $file_args_array;
	}

	/*
	* Update a file by
	* adding a new revision
	*/

	function shortcode_load_add_file_revision($args) {
		extract($args); //turn $args array into named variables

		global $wpdb;
		$table_name = $wpdb->prefix . 'shortcode_load'; 

		$sql = "SELECT name,type,revision,srcpath,minify FROM ".$table_name." WHERE id = ".(int)$id." LIMIT 1";
		$result = $wpdb->get_results($sql, ARRAY_A)[0];

		extract($result); //extract array to named variables, see $sql SELECT query

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
		}

		if($file_args['success'] == true) {
			$result = $this->shortcode_load_update_database_record( array('id' => (int)$id,'revision' => $new_revision));

			if($result['success'] == true) {
				$return_args = array('success' => true, 'id' => $id, 'name' => $name, 'type' => $type);
			} else {
				$return_args = array('success' => false);
			}
		} else {
			$return_args = array('success' => false);
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

		$sql = "SELECT id,name,revision FROM ".$table_name." WHERE type = 'js'";
		$result = $wpdb->get_results($sql, ARRAY_A);

		return $result;
	}

	/*
	* Return all entries of type 'css' in database
	*/
	function shortcode_load_get_styles() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shortcode_load'; 

		$sql = "SELECT id,name,revision FROM ".$table_name." WHERE type = 'css'";
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
	}	

	function shortcode_load_overview_scripts_callback() {
		$scripts = $this->shortcode_load_get_scripts();

		?>

		<table id="shortcode-load-scripts-table" class="table table-bordered table-striped shortcode-load-table">
			<thead>
				<th>Name</th>
				<th>Revision</th>
			</thead>
			<tbody>
				<?php
					foreach ($scripts as $script) { ?>
						<tr>
							<td><a href="?page=shortcode_load&amp;tab=tab_edit&amp;id=<?php echo $script['id']; ?>"><?php echo $script['name']; ?></a></td>
							<td><?php echo $script['revision']; ?></td>
						</tr>
					<?php } ?>
			</tbody>
		</table>

		<?php
	}

	function shortcode_load_overview_styles_callback() {
		$styles = $this->shortcode_load_get_styles();

		?>

		<table id="shortcode-load-styles-table" class="table table-bordered table-striped shortcode-load-table">
			<thead>
				<th>Name</th>
				<th>Revision</th>
			</thead>
			<tbody>
				<?php
					foreach ($styles as $style) { ?>
						<tr>
							<td><a href="?page=shortcode_load&amp;tab=tab_edit&amp;id=<?php echo $style['id']; ?>"><?php echo $style['name']; ?></a></td>
							<td><?php echo $style['revision']; ?></td>
						</tr>
					<?php } ?>
			</tbody>
		</table>

		<?php

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

	/* Edit file tab callbacks */

	function shortcode_load_edit_file_options_callback() {
		//TODO create a select dropdown in this function

		$id = intval($_GET['id']);
		$revision_override = intval($_GET['revision']);

		if($id) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'shortcode_load'; 

			$sql = "SELECT name,type,revision,srcpath,minpath FROM ".$table_name." WHERE id = '".$id."' LIMIT 1";
			$result = $wpdb->get_results($sql, ARRAY_A)[0];

			$options_edit_file = array();
			foreach ($result as $key => $value) {
				$options_edit_file[$key] = $value;
			}

			update_option('shortcode_load_edit_file_options', $options_edit_file);
		} else {
			//empty options array if no id was supplied to prevent old data from being presented
			update_option('shortcode_load_edit_file_options', array()); 
		}

		$options_edit_file = get_option( 'shortcode_load_edit_file_options' );

		//Get file content
		$file_src = $options_edit_file['srcpath'];
		$revision = $options_edit_file['revision'];
		$type = $options_edit_file['type'];

		if($revision > 0) {

			var_dump($revision);
			
			if($revision_override <= $revision) {
				$revision = $revision_override;
			}

			var_dump($revision);

			$srcname = basename($file_src, $type);
			$file_src_base = dirname($file_src) . '/';
			$file_src = $file_src_base . $srcname . $revision . "." . $type;
		}

		$content = $this->shortcode_load_get_file( $file_src );

		echo '<p>Current file: '.$options_edit_file['name'].'</p>';
		echo '<p>Current revision: '.$revision.'</p>';

		//Build Ace editor
		$container = '<div class="editor-container">';
		$editor = '<div id="editor">'.$content.'</div>';
		$container .= $editor . '</div>';

		echo $container;	
	}

	function shortcode_load_edit_file_source_options_callback() {
		$options_edit_file = get_option( 'shortcode_load_edit_file_options' );

		$current_id = intval ( $_GET['id'] ); //ensure integer value only

		/*Create a textarea to temporarily hold the raw data from Ace editor
		this data will then be processed when the page is reloaded again (Save Changes button is pressed)
		The textarea will be continously updated with javascript
		*/
		echo '<textarea id="edit_file_temporary_textarea" name="shortcode_load_edit_file_options[edit_file_temporary_textarea]">' . $options_edit_file[ 'edit_file_temporary_textarea' ] . '</textarea>';

		//We also need the id to refer to later, save this to a simple input field as well
		echo '<input type="text" id="edit_file_current_id" name="shortcode_load_edit_file_options[edit_file_current_id]" value="' . $current_id . '"/>';
		
	}

	function shortcode_load_editor_settings() {
		$options_edit_file = get_option( 'shortcode_load_edit_file_options' );

		//Ace editor settings
		$editor_theme = 'monokai';

		if($options_edit_file['type'] == 'js') {
			$editor_mode = 'javascript';
		} elseif ($options_edit_file['type'] == 'css') { 
			$editor_mode = 'css';
		}

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

					//Load editor settings
					$this->shortcode_load_editor_settings();
				}

				submit_button();
				?>
				
			</form>
		</div><!-- ./wrap -->
		<?php

	}
}

?>