<?php

use App\Controller;

class Test_Common {

	function run()
	{
		$test = new \Test();
		/** @var \Base $f3 */
		$f3 = \Base::instance();

		$news = new NewsModel();
		$news->load();

		$dummy = array(
			'title'=>'copy test',
			'text'=>'Lorem ipsum dolor sit amet.',
			'author'=>1,
			'tags'=>array(3)
		);
		$f3->set('record1', $dummy);
		$news->copyto('record2');

		$test->expect(
			$f3->exists('record2'),
			'copyto: raw record copied to hive'
		);

		$news->copyto_flat('news');

		$author = new AuthorModel();
		$author->load();
		$author->copyto_flat('author');
		$test->expect(
			is_array($f3->news['tags']) &&
			is_int($f3->news['tags'][0]) &&
			is_array($f3->author['news']) &&
			is_int($f3->author['news'][0]),
			'copyto_flat: record copied to hive with relations being flat arrays of IDs'
		);

		$news->reset();

		$news->copyfrom('record1');

		$test->expect(
			$news->title = 'copy test' &&
			$news->text = 'Lorem ipsum dolor sit amet.',
			'copyfrom: hydrate from hive key'
		);
		$test->expect(
			$news->author instanceof AuthorModel
			&& !$news->author->dry() &&
			$news->tags instanceof \DB\CortexCollection,
			'copyfrom: relations hydrated successful'
		);

		$test->expect(
			$news->get('author',true) == 1,
			'get raw data from relational field'
		);

		$news->reset();
		$news->copyfrom('record2','title;author');

		$test->expect(
			$news->title = 'Responsive Images' &&
			$news->get('author',true) == 2 &&
			$news->text == NULL,
			'copyfrom: limit fields with split-able string'
		);

		$news->reset();
		$news->copyfrom('record2',array('title'));

		$test->expect(
			$news->title = 'Responsive Images' && $news->text == NULL,
			'copyfrom: limit fields by array'
		);

		$news->reset();
		$news->copyfrom($dummy,function($fields) {
			return array_intersect_key($fields,array_flip(array('title')));
		});

		$test->expect(
			$news->title = 'copy test',
			'copyfrom: copy from array instead of hive key'
		);

		$test->expect(
			$news->title = 'copy test' && $news->text == NULL,
			'copyfrom: limit fields by callback function'
		);

		$all = $news->find();
		$allTitle = $all->getAll('title');

		$test->expect(
			count($allTitle) == 3 &&
			$allTitle[0] == 'Responsive Images' &&
			$allTitle[1] == 'CSS3 Showcase' &&
			$allTitle[2] == 'Touchable Interfaces',
			'collection getAll returns all values of selected field'
		);

		$newsByID = $all->getBy('_id');
		$test->expect(
			array_keys($newsByID) == array(1,2,3),
			'collection getBy sorts by given field'
		);

		$newsByAuthorID = $all->getBy('author',true);
		$test->expect(
			array_keys($newsByAuthorID) == array(2, 1) &&
			count($newsByAuthorID[2]) == 2 &&
			count($newsByAuthorID[1]) == 1,
			'collection getBy nested sort by author'
		);

		$allTitle = array();
		foreach($all as $record)
			$allTitle[] = $record->title;

		$test->expect(
			count($allTitle) == 3 &&
			$allTitle[0] == 'Responsive Images' &&
			$allTitle[1] == 'CSS3 Showcase' &&
			$allTitle[2] == 'Touchable Interfaces',
			'collection is traversable'
		);

		$news->reset();
		$news->load();
		$r = $news->cast(null,0);
		$test->expect($r['tags2']==null && is_int($r['author']),
			'simple cast without relations');

		$r = $news->cast(null,1);
		$test->expect(is_array($r['tags2']) && is_array($r['author']),
			'1-level nested cast');

		$r = $news->cast(null,2);
		$test->expect(is_array($r['author']['profile']),
			'2-level nested cast');

		$r = $news->cast(null,array('*'=>2));
		$test->expect(is_array($r['author']['profile']),
			'2-level nested cast, alternative');

		$r = $news->cast(null,array(
			'*'=>0,
			'author'=>0
		));
		$test->expect(is_array($r['author']) && $r['tags2']==null
			&& $r['author']['news']==null && $r['author']['profile']==null,
			'custom cast');

		$r = $news->cast(null,array(
			'*'=>0,
			'author'=>array(
				'*'=>1
			)
		));
		$test->expect(is_array($r['author']) && $r['tags2']==null
			&& is_array($r['author']['news']) && is_array($r['author']['profile']),
			'custom nested cast');

		$r = $news->cast(null,array(
			'*'=>0,
			'author'=>array(
				'*'=>0,
				'profile'=>0
			)
		));
		$test->expect(is_array($r['author']) && $r['tags2']==null
			&& $r['author']['news']==null && is_array($r['author']['profile'])
			&& is_int($r['author']['profile']['author']),
			'custom nested cast with exclusions');

		$r = $news->cast(null,array(
			'*'=>0,
			'author'=>array(
				'*'=>0,
				'profile'=>1
			)
		));
		$test->expect(is_array($r['author']) && $r['tags2']==null
			&& $r['author']['news']==null && is_array($r['author']['profile'])
			&& is_array($r['author']['profile']['author']),
			'custom multi-level nested cast');

		$filterA = array('foo1 = ? and bar1 = ?',10,20);
		$filterB = array('foo2 = ? and bar2 = ?',30,40);
		$filterC = array('foo3 = ? and bar3 = ?',50,60);
		$filter = $news->mergeFilter(array($filterA, $filterB, $filterC),'or');
		$test->expect($filter == array('( foo1 = ? and bar1 = ? ) or ( foo2 = ? and bar2 = ? ) or ( foo3 = ? and bar3 = ? )',
				10,20,30,40,50,60),
			'merge multiple filters');

		///////////////////////////////////
		return $test->results();
	}
}