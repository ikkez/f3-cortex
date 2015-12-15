<?php

class ProductsModel extends \DB\Cortex {

    protected static
        $fieldConf = array(
        'title' => array('type' => \DB\SQL\Schema::DT_VARCHAR128),
        'prize' => array('type' => \DB\SQL\Schema::DT_DECIMAL, 'default' => 123),
        'stock' => array('type' => \DB\Cortex::DT_INT, 'default' => 0),
        'description' => array('type' => \DB\SQL\Schema::DT_TEXT),
    );

    public function __construct() {
        $db = \Base::instance()->get('SQLDB');
        parent::__construct($db,'products');
    }

}


class ManufacturerModel extends \DB\Cortex {

    protected static
        $fieldConf = array(
        'title' => array('type' => \DB\SQL\Schema::DT_VARCHAR128),
        'location' => array('type' => \DB\SQL\Schema::DT_VARCHAR128),
    );

    public function __construct() {
        $db = \Base::instance()->get('SQLDB');
        parent::__construct($db,'manufacturer');
    }

}