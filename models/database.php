<?php

Class And_Load_Database {
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'and_load';
    }

    public function install() {
        global $wpdb;

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

    public function create($args) {
        global $wpdb;

        $wpdb->insert(
            $this->table_name,
            array( 
                'name' => $args['name'],
                'slug' => $args['slug'],
                'type' => $args['type'],
                'revision' => 0,
                'created_timestamp' => current_time('mysql', 1),
                'updated_timestamp' => current_time('mysql', 1),
            ), 
            array( 
                '%s', //name
                '%s', //slug
                '%s', //type
                '%s', //srcpath
                '%d', //revision
                '%s', //created_timestamp
                '%s' //updated_timestamp
            ) 
        );

        return ($wpdb->insert_id !== false);
    }

    function read($where, $order) {
        global $wpdb;

        $sql = "SELECT id, name, slug, type, revision, updated_timestamp, created_timestamp FROM " . $this->table_name . " WHERE ";

        $sql .= implode(' AND ', $where);

        $sql .= ' ORDER BY ';

        $sql .= implode(', ', $order);

        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    }

    public function update($id, $args) {
        global $wpdb;

        $result = $wpdb->update(
            $this->table_name, 
            array(
                'name' => $args['name'],
                'slug' => $args['slug'],
                'type' => $args['type'],
                'revision' => $args['revision']
            ), 
            array( 
                'id' => $id
            ), 
            array(
                '%d' //revision
            ),
            array(
                '%d' //id
            )
        );

        return ($result !== false);
    }

    public function delete($id) {
        global $wpdb;

        $result = $wpdb->delete( $this->table_name,
            array( 'id' => intval($id) ),
            array( '%d' )
        );

        return ($result !== false);
    }

    public function get_file_by_slug($slug, $revision = false) {
        $properties = array(
            'slug' => $slug,
        );

        if($revision !== false) {
            $properties['revision'] = $revision;
        }

        $result = $this->read($properties);
    }

    public function get_file_by_id($id, $revision = false) {
        $properties = array(
            'id' => $id,
        );

        if($revision !== false) {
            $properties['revision'] = $revision;
        }

        $result = $this->read($properties);
            try {
                global $wpdb;
                $table_name = $wpdb->prefix . 'and_load'; 

                $sql = "SELECT name,slug,type,revision,srcpath,minpath FROM ".$table_name." WHERE id = '" . $id . "' LIMIT 1";
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
    }
}
?>