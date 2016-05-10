<?php

Class And_Load_Models extends And_Load {
    public function __construct() {
        require( __DIR__ . '/database.php' );
        require( __DIR__ . '/minify.php' );

        $this->database = new And_Load_Database();
        $this->minify = new Minify();
    }
}