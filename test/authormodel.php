<?php

class AuthorModel extends \DB\Cortex {

	protected
		$fieldConf = [
			'name' => [
				'type' => \DB\SQL\Schema::DT_VARCHAR256
			],
			'mail' => [
				'type' => \DB\SQL\Schema::DT_VARCHAR256
			],
			'website' => [
				'type' => \DB\SQL\Schema::DT_VARCHAR256
			],
			'news' => [
				'has-many' => ['\NewsModel','author'],
			],
			'profile' => [
				'has-one' => ['\ProfileModel','author'],
			],
			'friends' => [
				'has-many' => ['\AuthorModel','friends'],
			],
	],
		$primary = 'id',
		$table = 'author',
		$db = 'DB';

}
