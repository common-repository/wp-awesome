<?php
namespace WPA\Module\FastLogin;

class FastLogin {
    public static function load() {
        \add_action( 'wp_head', array( self::class, 'wp_head' ) );
    }
    
    public static function wp_head() {
        if ( get_current_user_id() == 0) {
            wp_enqueue_script( 'wpa_fastlogin', WPA_URL.'modules/FastLogin/assets/FastLogin.js', array(), false, true);
        }
    }
}
