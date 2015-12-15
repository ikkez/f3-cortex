<?php

class BookModel extends \DB\Cortex {

    protected
        $fieldConf__ = array(
            'title' => array(
                'type' => \DB\SQL\Schema::DT_VARCHAR128
            ),
            'text' => array(
                'type' => \DB\SQL\Schema::DT_TEXT
            ),
            'text2' => array(
                'type' => \DB\SQL\Schema::DT_TEXT
            ),
        );

    protected
        $fluid = true,
        $table = 'books6',
        $db = 'DB';


 /*   function set_title($val) {
        var_dump('läuft');
        return $val.'.testing';
    }

    function get_title($val){
        return strrev($val);
    }
*/
}