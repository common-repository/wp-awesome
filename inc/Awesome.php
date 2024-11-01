<?php
namespace WPA;

class Awesome {
    
    /**
     * The hard coded config defaults.
     */
    private static $config_defaults = array(
        
        /**
         * Fast login is disabled by default. Users need to know about it to use
         * it anyway, so requiring them to enable it should not matter.
         */
        'WPAWESOME_ENABLE_FAST_LOGIN'       => false,
        
        'WPAWESOME_ENABLE_CONTEXT_MENUS'    => true,
        
        'WPAWESOME_ENABLE_HUGE_UPLOAD'      => true,
        
        );
        
    
    /**
     * Add listeners and load each of the enabled modules
     */
    public static function load() {

        /**
         * Load each of the add-ins. This will be refactored at some point.
         */

        if( self::config( 'WPAWESOME_ENABLE_FAST_LOGIN' ) )
            Module\FastLogin\FastLogin::load();
            
        if( self::config( 'WPAWESOME_ENABLE_CONTEXT_MENUS' ) )
            Module\ContextMenus\ContextMenus::load();
            
        if( self::config( 'WPAWESOME_ENABLE_HUGE_UPLOAD' ) )
            Module\HugeUpload\HugeUpload::load();
            
        /**
         * The Fluent module is under development. No documentation is provided.
         */
        if( self::config( 'WPAWESOME_ENABLE_FLUENT' ) ) {
            Module\Fluent\Fluent::load();
        }
        
        if( \is_admin() ) {
            \add_action( 'admin_init', array( self::class, 'admin_init' ));
            \add_action( 'admin_menu', array( self::class, 'admin_menu' ));
        }
    }
    
    public static function admin_init() {
        /**
         * Register some options that can be managed through WordPress admin interface
         */
        \register_setting( 'wpawesome-plugins', 'WPAWESOME_ENABLE_FAST_LOGIN' );
        \register_setting( 'wpawesome-plugins', 'WPAWESOME_ENABLE_CONTEXT_MENUS' );
        \register_setting( 'wpawesome-plugins', 'WPAWESOME_ENABLE_HUGE_UPLOAD' );
    }
    
    public static function admin_menu() {
        \add_options_page( 
            __( "HUEM Configuration Options", "wpa" ), 
            __( "HUEM", "wpa" ), 
            self::get_super_hero_capability(),
            'wp-awesome-options',
            array( self::class, 'options_page' )); 
    }
    
    public static function options_page() {
        include( WPA_ROOT . '/pages/options.php' );
    }
    
    /**
     * Returns true if the currently logged in user should be treated as a
     * super hero for this website.
     * 
     * The native WordPress function current_user_can() should be used for any
     * normal use case. This function should be used in the special case, where
     * we mean a user who has been given very special privileges on this site -
     * the super hero developer that is all mighty.
     * 
     * If there is no user that has been declared as all mighty, then this 
     * function will return true for site admins in multisite enviroments, and
     * true for admins in single site environments.
     * 
     * Multisite, this is equivalent to current_user_can( 'manage_network_options' )
     * Single site, this is the same as current_user_can( 'manage_options' )
     * 
     * @return boolean
     */
    public static function is_super_hero() {
        return \current_user_can( self::get_super_hero_capability() );
    }
    
    public static function get_super_hero_capability() {
        if( \is_multisite() ) {
            return 'manage_network_options';
        } else {
            return 'manage_options';
        }
    }
    
    /**
     * Get config option by first consulting the get_option() API of WordPress,
     * and if no value is specified there - it checks to see if a PHP constant has
     * been defined in wp-config.php. If no constant have been defined there,
     * it uses the provided $default value.
     * 
     * Example:
     *     Awesome::config('WPAWESOME_DISABLE_FAST_LOGIN', false)
     * 
     * @param $name     The name of the configuration constant
     * @param $default  The default value for the configuration option
     * @return mixed
     */
    public static function config( $name, $default = null ) {
        $value = \get_option( $name );
        if( $value !== false && $value != '' ) {
            return $value;
        }

        $options = \get_option( $name );
        if( isset( $options['WPAWESOME_CONFIG'] ) ) {
            return $options['WPAWESOME_CONFIG'];
        }
            
        if( defined( $name ) ) {
            return constant($name);
        }
        
        if( isset( self::$config_defaults[$name] ) ) {
            return self::$config_defaults[$name];
        }
        
        return $default;
    }
    
}