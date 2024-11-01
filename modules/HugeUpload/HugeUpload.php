<?php
namespace WPA\Module\HugeUpload;

/**
 * Replaces WordPress built in uploader with a javascript that uploads the file
 * in multiple parts.
 */
class HugeUpload {
    
    public static function load() {
        \add_action( 'wp_head', array( self::class, 'wp_head' ) );
        
        /**
         * Modify plupload so it uploads in chunks
         */
        \add_filter( 'plupload_init', array( self::class, 'plupload_init'), 1000 ); 
        \add_filter( 'plupload_default_settings', array( self::class, 'plupload_init' ));
        \add_filter( 'plupload_default_params', array( self::class, 'plupload_init' ));
        
        /**
         * Integrate with async-upload.php
         */
        \add_action( 'load-async-upload.php', array( self::class, 'load_async_upload' ));
        
        /**
         * Override any upload file size limitation that WordPress has.
         */
        \add_filter( 'upload_size_limit', array( self::class, 'upload_size_limit' ), -1000 );
        
        /**
         * Allow us to override the uploaded file name
         */
        \add_filter( 'wp_handle_upload_prefilter', array( self::class, 'wp_handle_upload_prefilter'), 1000 );

        /**
         * Clean temporary files, but don't slow down WordPress for public users.
         */
        if( mt_rand(0, 100) == 39 && is_admin() && get_current_user_id() != 0) {
            self::clean_temporary_files();
        }

    }
    
    public static function upload_size_limit() {
        return \wp_convert_hr_to_bytes(\WPA\Awesome::config('WPAWESOME_UPLOAD_LIMIT', '100000kb'));
    }
    
    public function get_temp_root($filename = null) {
        
        if($filename == null)
            return sys_get_temp_dir().'/'.\sanitize_file_name('wpawesome-hugeupload-'.$_SERVER['HTTP_HOST']);
        else
            return sys_get_temp_dir().'/'.\sanitize_file_name('wpawesome-hugeupload-'.$_SERVER['HTTP_HOST']).'/'.sanitize_file_name($filename);
    }
    
    public function perhaps_create_temp_folder($filename, $seed) {
        $root = self::get_temp_root();
        $path = self::get_temp_root($filename.substr(md5($seed), 0, 10));
        if(!is_dir($root))
            mkdir($root, 0700);
        if(!is_dir($path))
            mkdir($path, 0700);
        return $path;
    }
    
    public static function wp_handle_upload_prefilter($file) {
        if(isset($_REQUEST['chunk']) && isset($_REQUEST['chunks'])) {
            if( $_REQUEST['chunk'] == $_REQUEST['chunks']-1) {
                $file['name'] = $_REQUEST['name'];
            }
        }
        return $file;
    }

    public static function load_async_upload() {
        
        /**
         * Detect if this is a chunked upload. If that is the case,
         * we will take over the partially uploaded chunks and store them
         * as temporary files.
         * 
         * The final chunk will be altered, and we basically trick WordPress
         * into believing that the final chunk is actually the full file.
         */
         
        if(isset($_REQUEST['chunk']) && isset($_REQUEST['chunks'])) {
            $chunkNumber = intval($_REQUEST['chunk']); // 0-based
            $chunkCount = intval($_REQUEST['chunks']);
             
            $filename = $_REQUEST['name'];
            $path = self::perhaps_create_temp_folder($filename, $_REQUEST['_wpnonce']);
             
            if($chunkNumber < $chunkCount-1) {
                if(!move_uploaded_file($_FILES['async-upload']['tmp_name'], $path.'/'.$chunkNumber)) {
                    http_response_code(502); // Temporary problem
                    
                    die('<div class="error-div error">
	<a class="dismiss" href="#" onclick="jQuery(this).parents(\'div.media-item\').slideUp(200, function(){jQuery(this).remove();});">Dismiss</a>
	<strong>&#8220;'.htmlspecialchars($_REQUEST['name']).'&#8221; has failed to upload.</strong><br />WP Awesome encountered a problem.</div>');
                }
                die();
            } else {
                // This is the final chunk, so we modify it and allow WordPress
                // to handle it.
                // Keep the current chunk in memory, since we will be overwriting it to trick the move_uploaded_file function.
                $lastChunkData = file_get_contents($_FILES['async-upload']['tmp_name']);
                $fp = fopen($_FILES['async-upload']['tmp_name'], 'r+b');
                for($chunk = 0; $chunk < $chunkCount-1; $chunk++) {
                    if(!file_exists($path.'/'.$chunk)) {
                        http_response_code(500);
                        die('<div class="error-div error">
	<a class="dismiss" href="#" onclick="jQuery(this).parents(\'div.media-item\').slideUp(200, function(){jQuery(this).remove();});">Dismiss</a>
	<strong>&#8220;'.htmlspecialchars($_REQUEST['name']).'&#8221; has failed to upload.</strong><br />WP Awesome encountered a problem.</div>');
                        
                    }
                    $bytes = fwrite($fp, file_get_contents($path.'/'.$chunk));
                    unlink($path.'/'.$chunk);
                }
                rmdir($path);
                fwrite($fp, $lastChunkData);
                fclose($fp);

                \add_filter( 'sanitize_title', array( self::class, 'sanitize_title' ), -1000 );
            }
             
        }
        
    }
    
    public static function sanitize_title( $name ) {
        
        if($name == "blo" && isset($_REQUEST['chunk']) && isset($_REQUEST['chunks']) ) {
            $name = pathinfo($_REQUEST['name'], PATHINFO_FILENAME);
        }
        return $name;
    }
    
    /**
     * Delete orphaned temporary files by finding chunks that are older than 10
     * hours.
     */
    public static function clean_temporary_files() {
        
        $dir = self::get_temp_root();
        if(!is_dir($dir))
            return true;
            
        $files = glob($dir.'/*', GLOB_ONLYDIR);
        foreach($files as $filedir) {
            if(filemtime(filedir) < time()-36000) {
                foreach(glob($filedir.'/*') as $file)
                    @unlink($file);
                rmdir($filedir);
            }
        }

    }
    
    public static function plupload_init($info) {
        $info['chunk_size'] = \WPA\Awesome::config('WPAWESOME_HUGE_UPLOAD_CHUNK_SIZE', '1900kb');
        return $info;
        
    }
    
    public static function wp_head() {
        
        if ( get_current_user_id() != 0) {
            wp_enqueue_script( 'wpa_huge_upload', WPA_URL.'modules/HugeUpload/assets/HugeUpload.js', array(), false, true);
        }
        
    }
}
