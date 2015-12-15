<?php


class NewsModel2 extends \DB\Cortex {

    protected
        $table = 'news',
        $db = 'DB';

    public function __construct() {
        $this->fieldConf = array(
            'author' => array(
                'belongs-to-one' => '\AuthorModel',
            ),
            'tags' => array(
                'belongs-to-many' => '\TagModel',
            ),
            'tags2' => array(
                'has-many' => array('\TagModel', 'news'),
            ),
        );
        parent::__construct();
    }

}