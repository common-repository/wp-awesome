<?php
/*
Plugin Name:    HUEM: Huge Upload Enabler, mostly
Plugin URI:     http://plugins.wpmotor.no/
Description:    Removes upload size limitation, adds context menus and makes login faster.
Version:        1.0.1
Author:         Frode Børli
License:        GPLv2
Domain Path:    Languages
Text Domain:    wpa
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Define the auto loader
 */
spl_autoload_register( function($className) {
    if(substr($className, 0, 4) != 'WPA\\') {
        return;
    }
    
	if(substr($className, 0, 11) == 'WPA\\Module\\') {
		require( __DIR__ . '/modules/' . str_replace('\\', '/', substr($className, 11)) . '.php' );
	} else {
	    require( __DIR__ . '/inc/' . str_replace('\\', '/', substr($className, 4)) . '.php');
	}
	
} );

define( 'WPA_URL', plugin_dir_url( __FILE__ ));
define( 'WPA_ROOT', __DIR__ );
\WPA\Awesome::load();
