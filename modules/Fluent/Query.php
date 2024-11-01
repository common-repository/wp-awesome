<?php
namespace WPA\Module\Fluent;

class Query implements \IteratorAggregate {
    protected $datasetName;
    protected $wheres = array();
    protected $pageSize = 10;
    protected $offset = 0;
    protected $orderBy = 'date';
    protected $orderDesc = true;
    protected $search = null;
    protected $backendClass = null;
    
    /**
     * Provide a dataset name, e.g. table name or post type
     */
    public function __construct($datasetName) {
        $this->datasetName = $datasetName;
        $this->backendClass = Fluent::selectBackend($this->datasetName);
        if(!$this->backendClass)
            throw new Exception("Unable to find backend for data set '".$datasetName."'.", Exception::UNKNOWN_DATASET);
    }
    
    /**
     * Some backends support full text search, which is why the API supports it.
     */
    public function search($text) {
        $this->search = $text;
    }
    
    /**
     * A field name, an operator and an unescaped value is added to the query
     * using an AND combiner.
     */
    public function where($fieldName, $operator, $value) {
        switch($operator) {
            case "=" :
            case ">" :
            case "<" :
            case ">=" :
            case "<=" :
            case "<>" :
            case ":" : // A special operator, that may provide special functionality.
                break;
            default:
                throw new Exception("Unknown operator '$operator'.", Exception::ILLEGAL_OPERATOR);
        }
        $this->wheres[] = array($fieldName, $operator, $value);
        return $this;
    }
    
    /**
     * How many results to fetch
     */
    public function limit($pageSize) {
        $this->pageSize = $pageSize;
        return $this;
    }
    
    /**
     * Where to start fetching the results
     */
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * WordPress 'posts_where' filter on the WP_Query so that we can filter on 
     * more types. The $args argument on get_posts supports a wpa_wheres-key
     * which basically adds the arguments directly on the query.
     */
    public static function posts_where( $where, &$wp_query=null ) {
        global $wpdb;
        
        if(isset($wp_query->query) && isset($wp_query->query['wpa_wheres'])) {
            $wpa_wheres = $wp_query->query['wpa_wheres'];
            foreach($wpa_wheres as $spec) {
                $where .= ' AND wp_posts.'.$spec[0].' '.$spec[1].' \''.\esc_sql($spec[2]).'\'';
            }
        }
        
        return $where;
    }
    
    /**
     * Perform the query and fetch the articles
     */
    public function getIterator() {
        
    }
}