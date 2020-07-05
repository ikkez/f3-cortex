<?php

class BookModel extends \DB\Cortex {

    protected
        $fieldConf__ = [
            'title' => [
                'type' => \DB\SQL\Schema::DT_VARCHAR128
            ],
            'text' => [
                'type' => \DB\SQL\Schema::DT_TEXT
            ],
            'text2' => [
                'type' => \DB\SQL\Schema::DT_TEXT
            ],
    ];

    protected
        $fluid = true,
        $table = 'books6',
        $db = 'DB';

}