<?php

use MatthiasMullie\Minify;

Class And_Load_Minify {
    public function __construct() {
        require_once( dirname(__FILE__) . '/lib/minify/src/Minify.php' );
        require ( dirname(__FILE__) . '/lib/minify/src/JS.php' );
        require ( dirname(__FILE__) . '/lib/minify/src/CSS.php' );

        $this->js = new Minify\JS();
        $this->css = new Minify\CSS();
    }

    /*
    * Minify javascript code
    */
    public function javascript($content) {
        $this->js->add($content);
        return $this->js->minify();
    }

    /*
    * Minify css code
    */
    public function css($content) {
        $this->css->add($content);
        return $this->css->minify();
    }
}
?>