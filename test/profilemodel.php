<?php

class ProfileModel extends \DB\Cortex {

	protected
		$fieldConf = [
			'message' => [
				'type' => \DB\SQL\Schema::DT_TEXT
			],
			'image' => [
				'type' => \DB\SQL\Schema::DT_VARCHAR256
			],
			'author' => [
				'belongs-to-one' => '\AuthorModel'
			]
	],
//		$primary = 'profile_id',
		$table = 'profile',
		$db = 'DB';

}