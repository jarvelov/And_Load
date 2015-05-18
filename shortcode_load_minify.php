<?php

use MatthiasMullie\Minify;

Class ShortcodeLoad_Minify extends ShortcodeLoad {

    function __construct() {
        if( class_exists( 'ShortcodeLoad' ) ) {
            if ( ! ( class_exists( 'Minify' ) ) ) {
                try {
                    require( dirname(__FILE__) . '/lib/minify/src/Minify.php' );
                } catch(Exception $e) {
                    //var_dump($e);
                    throw new Exception("Error in loading minify library files", 6);
                }
            }
        } else {
            throw new Exception("Class ShortcodeLoad is not loaded. This function can not be called outside it's environment", 5);
        }
    }

    /*
    * Minify file content using Matthias Mullie's minify library
    */
    function shortcode_load_minify_minify_file($content, $type) {
        switch ($type) {
            case 'js':
                try {
                    require ( dirname(__FILE__) . '/lib/minify/src/JS.php' );
                    $minifier = new Minify\JS();
                } catch(Exception $e) {
                    //var_dump($e);
                    throw new Exception("Error initializing minify library for JavaScript file", 7);
                }
                break;
            
            case 'css':
                try {
                    require ( dirname(__FILE__) . '/lib/minify/src/CSS.php' );
                    $minifier = new Minify\JS();
                } catch(Exception $e) {
                    //var_dump($e);
                    throw new Exception("Error initializing minify library for JavaScript file", 8);
                }
                break;

            default:
                throw new Exception("Error: File type is invalid in minify library initialization", 9);
                break;
        }

        if( isset($minifier) ) {
            try {
                $minifier->add($content);
                $minified_content = $minifier->minify();
                return $minified_content;    
            } catch(Exception $e) {
                //var_dump($e);
                throw new Exception("Error minifying file content", 10);
            }       
        } else {
            throw new Exception("Error: Minify library is not initialized when trying to minify file.", 11);
        }
    }
}

?>