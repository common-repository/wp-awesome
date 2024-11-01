<?php
namespace WPA\Module\Fluent\Backends;

class WP_Query implements BackendInterface {
    
    protected $query;
    
    public function __construct(WPA\Module\Fluent\Query $query) {
        $this->query = $query;
    }

    public function buildWPQuery() {
        $args = array(
            "post_type" => $this->postType,
            "orderby" => $this->orderBy,
            "order" => $this->orderDesc ? "DESC" : "ASC",
            "ignore_sticky_posts" => true,
            "offset" => $this->offset,
            "posts_per_page" => $this->pageSize,
            "wpa_wheres" => array(),
            );

        // Try to map the wheres to appropriate WP_Query arguments        
        $legalSqlOperators = array("<", ">", "<>", "<=", ">=", "=");
        $directToWPQuery = array( 'title', 'author', 'author_name', 'cat', 'category_name', 'tag', 'tag_id' );
        foreach($this->wheres as $spec) {
            
            if( in_array( $spec[0], $directToWPQuery )) {
                if($spec[1] == '=' && !is_array($spec[2])) {
                    $args[$spec[0]] = $spec[2];
                } else {
                    // Lot's of special cases, we'll try to standardize
                    if($spec[1] == '=' ) {
                        switch($spec[0]) {
                            case 'author' :
                                $args['author__in'] = $spec[2];
                                break;
                            case 'cat' :
                                $args['category__in'] = $spec[2];
                                break;
                            case 'tag' :
                                $args['tag_slug__in'] = $spec[2];
                                break;
                            case 'tag_id' :
                                $args['tag__in'] = $spec[2];
                                break;
                            default :
                                throw new Exception("Field '".$spec[0]."' can't use array as value");
                        }
                    } else if($spec[1] == '<>') {
                        switch($spec[0]) {
                            case 'author' :
                                $args['author__not_in'] = $spec[2];
                                break;
                            case 'cat' :
                                $args['category__not_in'] = $spec[2];
                                break;
                            case 'tag' :
                                $args['tag__not_in'] = $spec[2];
                                break;
                            case 'title' :
                                $args['wpa_wheres'] = array( 'post_title', '<>', $spec[2]);
                                break;
                            default :
                                throw new Exception("Field '".$spec[0]."' can't use the '<>' operator.");
                        }
                    } else if($spec[1] == '<' || $spec[1] == '<=' || $spec[1] == '>' || $spec[1] == '>=' ) {
                        switch($spec[0]) {
                            case 'title' :
                                $args['wpa_wheres'] = array( 'post_title', $spec[1], $spec[2]);
                                break;
                            default :
                                throw new Exception("Field '".$spec[0]."' can't use the '".$spec[1]."' operator.");
                        }
                    } else {
                        throw new Exception("Unsupported combination of ");
                    }
                }
            }
            
            
            switch($spec[0]) {
                case 'post_title' :
                    if(in_array( $spec[1], $legalSqlOperators )) {
                        $args['wpa_wheres'][] = $spec;
                    } else {
                        throw new Exception("Illegal operator '".$spec[1]."' used in Query for field '".$spec[0]."'");
                    }
                    break;
                case 'post_name' :
                case 'name' :
                    if( $spec[1] == '=' ) {
                        $args['name'] = $spec[2];
                    } else if( in_array( $spec[1], $legalSqlOperators )) {
                        $args['wpa_wheres'][] = array( 'post_name', $spec[1], $spec[2] );
                    } else {
                        throw new Exception("Illegal operator '".$spec[1]."' used in Query for field '".$spec[0]."'");
                    }
                    break;
                case 'author' :
                case 'post_author' :
                    if( $spec[1] == '=' ) {
                        $args['author'] = $spec[2];
                    }
                    break;
                    
                default :
                    
            }
        }


        $query = new \WP_Query();
        return $query->query($args);
    }
    
    
    
}