<?php

class NewsModel extends \DB\Cortex {

	protected
		$fieldConf = array(
			'title' => array(
				'type' => \DB\SQL\Schema::DT_VARCHAR128
			),
			'text' => array(
				'type' => \DB\SQL\Schema::DT_TEXT,
			),
			'author' => array(
				'belongs-to-one' => '\AuthorModel',
			),
			'tags' => array(
				'belongs-to-many' => '\TagModel',
			),
			'tags2' => array(
				'has-many' => array('\TagModel','news','news_tags'),
//				'has-many' => array('\TagModel','news'),
			),
			'created_at' => array(
				'type' => \DB\SQL\Schema::DT_DATETIME
			),

		),
//		$primary='nid',
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