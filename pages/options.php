<div class="wrap">
    <h1><?php echo _e( "HUEM Options", "wpa" ); ?></h1>
    
    <h2>Enabled Features</h2>
    <p><?php echo _e( "HUEM comes with the following features that you can enable, disable or use default for.", "wpa" ); ?></p>
    
    <form method="post" action="options.php">
        
        <?php settings_fields( 'wpawesome-plugins' ); ?>
        
        <?php do_settings_sections( 'wpawesome-plugins' ); ?>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( "Context Menus", "wpa" ); ?></th>
                <td>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_CONTEXT_MENUS') == '1' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_CONTEXT_MENUS' value="1"> <?php echo _e( "Enabled", "wpa" ); ?></label>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_CONTEXT_MENUS') == '0' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_CONTEXT_MENUS' value="0"> <?php echo _e( "Disabled", "wpa" ); ?></label>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_CONTEXT_MENUS') == '' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_CONTEXT_MENUS' value=""> <?php echo _e( "Use Default", "wpa" ); ?></label>
                    </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( "Fast Login", "wpa" ); ?></th>
                <td>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_FAST_LOGIN') == '1' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_FAST_LOGIN' value="1"> <?php echo _e( "Enabled", "wpa" ); ?></label>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_FAST_LOGIN') == '0' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_FAST_LOGIN' value="0"> <?php echo _e( "Disabled", "wpa" ); ?></label>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_FAST_LOGIN') == '' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_FAST_LOGIN' value=""> <?php echo _e( "Use Default", "wpa" ); ?></label>
                    </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( "Huge Upload", "wpa" ); ?></th>
                <td>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_HUGE_UPLOAD') == '1' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_HUGE_UPLOAD' value="1"> <?php echo _e( "Enabled", "wpa" ); ?></label>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_HUGE_UPLOAD') == '0' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_HUGE_UPLOAD' value="0"> <?php echo _e( "Disabled", "wpa" ); ?></label>
                    <label><input <?php if( get_option('WPAWESOME_ENABLE_HUGE_UPLOAD') == '' ) echo "checked"; ?> type='radio' name='WPAWESOME_ENABLE_HUGE_UPLOAD' value=""> <?php echo _e( "Use Default", "wpa" ); ?></label>
                    </td>
            </tr>
        </table>
        
        
        <?php submit_button(); ?>
        
    </form>
    
</div>