<?php

class NewsModel extends \DB\Cortex {

	protected
		$fieldConf = [
			'title' => [
				'type' => \DB\SQL\Schema::DT_VARCHAR128
			],
			'text' => [
				'type' => \DB\SQL\Schema::DT_TEXT,
			],
			'author' => [
				'index'=>true,
				'belongs-to-one' => '\AuthorModel',
			],
			'tags' => [
				'belongs-to-many' => '\TagModel',
			],
			'tags2' => [
				'has-many' => ['\TagModel','news','news_tags',
					'relField' => 'neeeews'
				],
			],
			'created_at' => [
				'type' => \DB\SQL\Schema::DT_DATETIME
			],
			'options' => [
				'type' => self::DT_JSON
			],

	],
		$table = 'news',
		$db = 'DB';

	public function getBySQL($query) {
		$result = $this->db->exec($query);
		$cx = new \DB\CortexCollection();
		foreach($result as $row) {
			$new = $this->factory($row);
			$cx->add($new);
			unset($new);
		}
		return $cx;
	}

}