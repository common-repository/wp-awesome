<?php
namespace WPA\Module\ContextMenus;

class ContextMenus {
    
    public static function load() {
        \add_action( 'wp_head', array( self::class, 'wp_head' ));
        \add_action( 'wp_enqueue_scripts', array( self::class, 'wp_enqueue_scripts' ));
        \add_action( 'wp_ajax_wpa_context_menu', array( self::class, 'ajax_context_menu' ));
        \add_filter( 'wpa_context_menu', array( self::class, 'context_menu' ), 0, 2);
    }
    
    public static function get_attachment_id( $url ) {
    	$attachment_id = 0;
    	$dir = wp_upload_dir();
    	if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?
    		$file = basename( $url );
    		$query_args = array(
    			'post_type'   => 'attachment',
    			'post_status' => 'inherit',
    			'fields'      => 'ids',
    			'meta_query'  => array(
    				array(
    					'value'   => $file,
    					'compare' => 'LIKE',
    					'key'     => '_wp_attachment_metadata',
    				),
    			)
    		);
    		$query = new \WP_Query( $query_args );
    		if ( $query->have_posts() ) {
    			foreach ( $query->posts as $post_id ) {
    				$meta = wp_get_attachment_metadata( $post_id );
    				$original_file       = basename( $meta['file'] );
    				$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
    				if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
    					$attachment_id = $post_id;
    					break;
    				}
    			}
    		}
    	}
    	return $attachment_id;
    }
    
    public static function wp_enqueue_scripts() {
        /**
         * The script for showing context menus
         */
        \wp_register_script( 'wpa_context_menus', WPA_URL.'modules/ContextMenus/assets/ContextMenus.js', array( 'jquery' ) );
        $settings = array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
            );
        \wp_localize_script( 'wpa_context_menus', 'settings', $settings );
        if ( get_current_user_id() != 0) {
            wp_enqueue_script( 'wpa_context_menus' );
            wp_enqueue_style( 'wpa_context_menus_css', WPA_URL.'modules/ContextMenus/assets/ContextMenus.css' );
        }
    }
    
    public static function wp_head() {
    }
    
    /**
     * Called when the user right clicks anything, while logged in
     */
    public static function ajax_context_menu() {
        ob_clean();
        header("Content-Type: application/json");
        $info = array(
            "trail" => $_POST['trail'],
            "url" => $_POST['url'],
        );
        $menu = array(
            'items' => array(),
        );
        $menu = \apply_filters( 'wpa_context_menu', $menu, $info );
        echo json_encode($menu);
        die();
    }
    
    /**
     * Adds the default context menu items to the class
     */
    public static function context_menu($menu, $info) {
        
        /**
         * Collect known IDs such as widget ids that can be used to identify content
         */
        $widgetIds = array();
        $sidebarWidgets = wp_get_sidebars_widgets(); // Note: This is using a WordPress function marked as private
        foreach($sidebarWidgets as $sidebarId => $widgets) {
            if($widgets && $sidebarId != "wp_inactive_widgets") {
                foreach($widgets as $widgetId) {
                    if(!isset($widgetIds[$widgetId]))
                        $widgetIds[$widgetId] = array();
                    $widgetIds[$widgetId][] = $sidebarId;
                }
                $widgetId = null;
            }
        }
        $siteName = get_bloginfo('name');
        $siteDescription = get_bloginfo('description');
        
        
        $postId = null;
        $postIdComments = null;
        $commentId = null;
        $widgetId = null;
        $editDesign = null;
        $attachmentId = null;
        $editMenus = null;
        $editSiteName = null;
        
        foreach($info['trail'] as $tag) {
            
            if(isset($tag['innerHTML'])) {
                if($tag['innerHTML'] != "" && ($tag['innerHTML'] == $siteName || $tag['innerHTML'] == $siteDescription)) {
                    $menu['items']["edit_site_info"] = array(
                        "weight" => 10,
                        "label" => __( "Edit Site Info", "wpa" ),
                        "url" => admin_url("options-general.php"),
                    );
                }
                
            }
            if($tag['tagName'] == 'IMG' && isset($tag['src']) ) {
                $id = null;
                if(!$attachmentId) {
                    if($id = self::get_attachment_id( $tag['src'] )) {
                        $attachmentId = $id;
                    }
                }
            }
            
            if($tag['tagName'] == 'A' && isset($tag['href'])) {
                $href = $tag['href'];
                
                if( 0 !== ($id = url_to_postid( $href ))) {
                    $postId = $id;
                }
                
                $parts = explode("#", $href);
                
                if(isset($parts[1])) {
                    if($parts[1]=='comments') {
                            $postIdComments = $postId;
                    } else if(substr($parts[1], 0, 8) == 'comment-') {
                        if(!$commentId) {
                            $commentId = intval(substr($parts[1], 8));
                        }
                    }
                }
                else if(!$postId) {
                    if (0 !== ($id = url_to_postid($href))) {
                        $postId = $id;
                    }
                }
            }
            
            if(isset($tag['className'])) {
                $classNames = explode(" ", $tag['className']);
                if(!$postId) {
                    if(in_array('post', $classNames)) {
                        foreach($classNames as $className) {
                            if(substr($className, 0, 5) == 'post-') {
                                $postId = intval(substr($className, 5));
                            }
                        }
                    }
                }
                if(!$postId) {
                    if(in_array('page', $classNames)) {
                        foreach($classNames as $className) {
                            if(substr($className, 0, 8)=='page-id-') {
                                $postId = intval(substr($className, 8));
                            }
                        }
                    }
                }
                if(!$editMenus) {
                    if(in_array('page_item', $classNames) || in_array('current-menu-item', $classNames)) {
                        $editMenus = true;
                    }
                }
            }

            if(!$editDesign) {
                if(isset($tag['id'])) {
                    if($tag['id']=='site-title') {
                        $classNames = $info['trail'][sizeof($info['trail'])-2];
                        if(isset($classNames['className'])) {
                            $classNames = explode(" ", $classNames['className']);
                            if(in_array('customize-support', $classNames)) {
                                $editDesign = true;                                
                            }
                        }
                    }
                }
                if($tag['tagName'] == "IMG" && isset($tag['alt']) && $tag['alt']==$siteName) {
                    $editDesign = true;
                }
            }
            
            if(!$postId) {
                if(isset($tag['id']) && substr($tag['id'], 0, 5) == 'post-') {
                    $postId = intval(substr($tag['id'], 5));
                }
            }
            
            if(!$commentId) {
                if(isset($tag['id']) && substr($tag['id'], 0, 12) == 'div-comment-') {
                    $commentId = intval(substr($tag['id'], 12));
                }
            }
            
            if(!$widgetId) {
                if(isset($tag['id']) && isset($widgetIds[$tag["id"]])) {
                    $widgetId = $tag['id'];
                }
            }
        }
        
        if(!$postId) {
            if( 0 !== ($id = url_to_postid( $info['url'] ))) {
                $postId = $id;
            }
        }
        
        if($postId && current_user_can( 'edit_post', $postId )) {
            // Menu items for posts
            $menuItems = array();
            $post = get_post($postId);
            
            $post_type_object = get_post_type_object( $post->post_type );
            if($post_type_object) {
                $menuItems["edit_post"] = array(
                    "weight" => 10,
                    "label" => $post_type_object->labels->edit_item,
                    "url" => get_edit_post_link($postId, 'json'),
                    "title" => get_the_title($post),
                    );
            }
            
            if (get_comments_number( $postId ) && current_user_can("moderate_comments") ) {
                $menuItems["manage_comments"] = array(
                    "weight" => 20,
                    "label" => __( 'Manage Comments' ),
                    "url" => admin_url( "edit-comments.php?p=" . $postId ),
                    );
            }
            
            if(sizeof($menuItems) > 0) {
                $menu['items']["post"] = array(
                    "weight" => 10,
                    "label" => __( "Post", "wpa" ),
                    "items" => $menuItems,
                );
            }
        }
        
        if($commentId && current_user_can( 'edit_comment', $commentId)) {
            // Menu items for comments
            $menuItems = array();
    		$del_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "delete-comment_$commentId" ) );
    		$approve_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "approve-comment_$commentId" ) );
    
    		$url = "comment.php?c=" . $commentId;
    
    		$unapprove_url = $url . "&action=unapprovecomment&$approve_nonce";
    		$spam_url = $url . "&action=spamcomment&$del_nonce";
    		$trash_url = $url . "&action=trashcomment&$del_nonce";
    		$delete_url = $url . "&action=deletecomment&$del_nonce";
            
            $menuItems["edit"] = array(
                "weight" => 10,
                "label" => __( "Edit Comment" ),
                "url" => admin_url( "comment.php?action=editcomment&c=" . $commentId ),
                );
                
            $menuItems["unapprove"] = array(
                "weight" => 20,
                "label" => __( "Unapprove" ),
                "url" => admin_url( $unapprove_url ),
                );
                
            $menuItems["spam"] = array(
                "weight" => 30,
                "label" => __( "Spam" ),
                "url" => admin_url( $spam_url ),
                );

            $menuItems["trash"] = array(
                "weight" => 40,
                "label" => __( "Trash" ),
                "url" => admin_url( $trash_url ),
                );
                
            $menuItems["delete"] = array(
                "weight" => 50,
                "label" => __( "Delete" ),
                "url" => admin_url( $delete_url ),
                );
                
            if(sizeof($menuItems) > 0) {
                $menu['items']["comment"] = array(
                    "weight" => 10,
                    "label" => __( "Comments", "wpa" ),
                    "items" => $menuItems,
                );
            }
        }
        
        if($attachmentId) {
            
            $attachment = get_post($attachmentId);
            $post_type_object = get_post_type_object( $attachment->post_type );
            if($post_type_object) {
                $menu['items']["edit_attachment"] = array(
                    "weight" => 15,
                    "label" => $post_type_object->labels->edit_item,
                    "url" => get_edit_post_link($attachment, 'json'),
                    "title" => get_the_title($post),
                    );
            }
        }
        
        if($editDesign && current_user_can( 'edit_theme_options' )) {
            $menu['items']['edit_theme'] = array(
                "weight" => 100,
                "label" => __( "Customize Theme" , "wpa" ),
                "url" => admin_url('customize.php?url=' . rawurlencode($info['url'])),
                );
        }
        
        
        if($widgetId && current_user_can( 'edit_theme_options' )) {
            $menu['items']['edit_widgets'] = array(
                "weight" => 50,
                "label" => __( "Edit Widgets", "wpa" ),
                "url" => admin_url("widgets.php"),
                );
        }
        
        if($editMenus && current_user_can( 'edit_theme_options' )) {
            $menu['items']['edit_menus'] = array(
                "weight" => 60,
                "label" => __( "Edit Menus" ),
                "url" => admin_url("nav-menus.php")
                );
        }

        return $menu;
    }
}