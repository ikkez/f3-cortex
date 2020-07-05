<?php

class TagModel extends \DB\Cortex {

	protected
		$fieldConf = [
			'title' => [
				'type' => \DB\SQL\Schema::DT_VARCHAR128
			],
			'news' => [
				'has-many' => ['\NewsModel','tags2','news_tags',
					'relField' => 'taaags'
				],
			],
		],
		$table = 'tags',
		$db = 'DB';

}