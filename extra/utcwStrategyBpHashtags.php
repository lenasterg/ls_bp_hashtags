<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of utcwStrategyBpHashtags
 *
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 1
 * @since 8 Μαϊ 2014
 *
 */
class utcwStrategyBpHashtags extends UTCW_SelectionStrategy {

    protected $plugin ;

    public function __construct( ) {
        $this->plugin = $plugin ;
    }

    public function getData() {

        // Return an array of objects with the keys:
        // term_id, name, slug, count, taxonomy

        $result = array () ;

        $foo = new stdClass ;

        $foo->term_id = 1 ;
        $foo->name = 'test' ;
        $foo->slug = 'test' ;
        $foo->count = 4 ;
        $foo->taxonomy = '' ;

        $result = ls_bp_hashtags_get_hashtags() ;
       array_map( function($v) {
            $v->term_id = '0' ;
            $v->slug = $v->link ;
            $v->taxonomy = '' ;

        } , $result ) ;



        return $result ;
    }

    public function cleanupForDebug() {
        // Remove sensitive data before debug output
    }

}

