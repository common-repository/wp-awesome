<?php
namespace WPA\Module\Fluent;

class Fluent  {
    
    protected static $populatedNativeBackends = false;
    protected static $backends = array();
    
    public static function load() {
        \add_filter( 'posts_where', array( Query::class, 'posts_where' ), 1000, 2);
    }
    
    /**
     * Register a data set backend. For example the data set named 'posts' 
     * should be handled by WPA\Module\Fluent\Backends\WP_Query::class
     * 
     * Fluent::registerDataset('posts', WPA\Module\Fluent\Backends\WP_Query::class)
     */
    public static function registerDataset($datasetName, $backendClass) {
        self::$backends[$datasetName] = $backendClass;
    }
    
    protected static function populateNativeBackends() {
        if(self::$populatedNativeBackends)
            return;
        self::$populatedNativeBackends = true;
        $postTypes = \get_post_types();
        foreach($postTypes as $postType) {
            self::registerDataset($postType, Backends\WP_Query::class);
        }
    }
    
    public static function selectBackend($datasetName) {
        if(isset(self::$backends[$datasetName]))
            return self::$backends[$datasetName];
        if(!self::$populatedNativeBackends) {
            self::populateNativeBackends();
            return self::selectBackend($datasetName);
        }
        return null;
    }
}