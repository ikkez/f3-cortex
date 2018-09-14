<?php

use App\Controller;

class Test_Filter {

	function run($db,$type)
	{
		$test = new \Test();

		// setup
		///////////////////////////////////
		$author = new \AuthorModel();
		$news = new \NewsModel();
		$profile = new \ProfileModel();
		$tag = new \TagModel();

		$ac=$author::resolveConfiguration();
		$author_pk = (is_int(strpos($type,'sql'))?$ac['primary']:'_id');

		$nc=$news::resolveConfiguration();
		$news_pk = (is_int(strpos($type,'sql'))?$nc['primary']:'_id');

		$tc=$tag::resolveConfiguration();
		$tag_pk = (is_int(strpos($type,'sql'))?$tc['primary']:'_id');

		$authorIDs = $author->find()->getAll('_id');
		$all = $news->find(null,['order'=>'id']);
		$newsIDs = $all->getAll('_id');
		sort($newsIDs);
		$profileIDs = $profile->find()->getAll('_id');
		$tagIDs = $tag->find()->getAll('_id');

		// add another relation
		$news->load(array('title = ?','CSS3 Showcase'));
		$author->load(array($author_pk.' = ?',$authorIDs[0]));
		$news->author = $author;
		$news->save();
		$news->reset();
		$author->reset();

		$page=$news->paginate(0,2,null,['order'=>'title DESC']);
		$test->expect(
			$page['subset'][0]->get('title')=='Touchable Interfaces' &&
			$page['subset'][1]->get('title')=='Responsive Images',
			$type.': pagination: first page'
		);
		$page=$news->paginate(1,2,null,['order'=>'title DESC']);
		$test->expect(
			$page['subset'][0]->get('title')=='CSS3 Showcase',
			$type.': pagination: last page'
		);

		// has-filter on belongs-to relation
		///////////////////////////////////
		$result = $author->has('news', array('title like ?', '%Image%'))->afind();

		$test->expect(
			count($result) == 1 &&
			$result[0]['name'] == 'Johnny English',
			$type.': has filter on many-to-one field'
		);
		\Matrix::instance()->sort($result[0]['news'],'title');
		$test->expect(
			count($result[0]['news']) == 2 &&
			$result[0]['news'][0]['title'] == 'CSS3 Showcase' &&
			$result[0]['news'][1]['title'] == 'Responsive Images',
			$type.': has filter does not prune relation set'
		);

		$result = $news->has('author', array('name = ?', 'Johnny English'))->afind(null,array('order'=>'title DESC'));
		$test->expect(
			count($result) == 2 && // has 2 news
			$result[0]['title'] == 'Responsive Images' &&
			$result[1]['title'] == 'CSS3 Showcase',
			$type.': has filter on one-to-many field'
		);

		// add another profile
		$profile->message = 'Beam me up, Scotty!';
		$profile->author = $authorIDs[2];
		$profile->save();
		$profile->reset();

		$result = $author->has('profile',array('message LIKE ?','%Scotty%'))->afind();
		$test->expect(
			count($result) == 1 &&
			$result[0]['name'] == 'James T. Kirk' &&
			$result[0]['profile']['message'] == 'Beam me up, Scotty!',
			$type.': has filter on one-to-one field'
		);

		$result = $profile->has('author',array('name LIKE ?','%Kirk%'))->afind();
		$test->expect(
			count($result) == 1 &&
			$result[0]['message'] == 'Beam me up, Scotty!' &&
			$result[0]['author']['name'] == 'James T. Kirk',
			$type.': has filter on one-to-one field, inverse'
		);

		// add mm tags
		$news->load(array('title = ?','Responsive Images'));
		$news->tags2 = array($tagIDs[0],$tagIDs[1]);
		$news->save();
		$news->load(array('title = ?','CSS3 Showcase'));
		$news->tags2 = array($tagIDs[1],$tagIDs[2]);
		$news->save();
		$news->reset();

		$result = $news->has('tags2',array('title like ?','%Design%'))->find();
		$test->expect(
			count($result) == 1 &&
			$result[0]['title'] == 'Responsive Images',
			$type.': has filter on many-to-many field'
		);

		$result = $news->has('tags2',array('title = ?','Responsive'))->find();
		$test->expect(
			count($result) == 2 &&
			$result[0]['title'] == 'Responsive Images' &&
			$result[1]['title'] == 'CSS3 Showcase',
			$type.': has filter on many-to-many field, additional test'
		);


		$result = $tag->has('news',array('title = ?','Responsive Images'))->find();
		$test->expect(
			count($result) == 2 &&
			$result[0]['title'] == 'Web Design' &&
			$result[1]['title'] == 'Responsive',
			$type.': has filter on many-to-many field, inverse'
		);

		// add another tag
		$news->load(array('title = ?', 'Touchable Interfaces'));
		$news->tags2 = array($tagIDs[1]);
		$news->save();
		$news->reset();

		$tag->has('news',array('text LIKE ? and title LIKE ?', '%Lorem%', '%Interface%'));
		$result = $tag->find();
		$test->expect(
			count($result) == 1 &&
			$result[0]['title'] == 'Responsive',
			$type.': has filter with multiple conditions'
		);

		$news->has('tags2', array('title = ? OR title = ?', 'Usability', 'Web Design'));
		$result = $news->afind(array('text = ?', 'Lorem Ipsun'));
		$test->expect(
			count($result) == 1 &&
			$result[0]['title'] == 'Responsive Images',
			$type.': find with condition and has filter'
		);

		$news->load(array('title = ?', 'Responsive Images'));
		$news->author = $authorIDs[1];
		$news->save();
		$news->reset();


		$news->has('tags2', array('title = ? OR title = ?', 'Usability', 'Web Design'));
		$news->has('author', array('name = ?', 'Ridley Scott'));
		$result = $news->afind();
		$test->expect(
			count($result) == 1 &&
			$result[0]['title'] == 'Responsive Images',
			$type.': find with multiple has filters on different relations'
		);

		// add another news to author 2
		$news->load(array($news_pk.' = ?',$newsIDs[2]));
		$news->author = $authorIDs[1];
		$news->save();

		$news->reset();
		$news->has('author', array('name = ?', 'Ridley Scott'));
		$news->load(null,array('order'=>'title'));
		$res = array();
		while (!$news->dry()) {
			$res[] = $news->title;
			$news->next();
		}

		$test->expect(
			count($res) == 2 &&
			$res[0] == 'Responsive Images' &&
			$res[1] == 'Touchable Interfaces'
			,
			$type.': has filter in load context'
		);

		$news->reset();
		$news->fields(array('title'));
		$news->load();

		$test->expect(
			!empty($news->title) &&
			empty($news->author) &&
			empty($news->text) &&
			empty($news->tags) &&
			empty($news->tags2),
			$type.': use a whitelist to restrict fields'
		);

		unset($news);
		$news = new \NewsModel();

		$news->fields(array('title','tags','tags2','author'),true);
		$news->load();

		$test->expect(
			empty($news->title) &&
			empty($news->author) &&
			!empty($news->text) &&
			empty($news->tags) &&
			empty($news->tags2),
			$type.': use a blacklist to restrict fields'
		);

		unset($news);
		$news = new \NewsModel();

		$news->fields(array('tags.title'));
		$news->load();

		$test->expect(
			!empty($news->tags[0]->title) &&
			empty($news->tags[0]->news),
			$type.': set restricted fields to related mappers'
		);

		$news->fields(array('tags2'));
		$news->filter('tags2',null,array('order'=>'title ASC'));
		$news->load(array('title = ?','Responsive Images'));
		$test->expect(
			$news->tags2[0]->title == 'Responsive' &&
			$news->tags2[1]->title == 'Web Design',
			$type.': filter with sorting of related records'
		);

		unset($news);
		$news = new \NewsModel();

		// get all tags sorted by their usage in news articles
		$tag->reset();
		$tag->countRel('news');
		$result = $tag->find(null,array('order'=>'count_news DESC, title'))->castAll(0);

		$test->expect(
			$result[0]['title'] == 'Responsive' &&
			$result[0]['count_news'] == 3 &&
			$result[1]['title'] == 'Usability' &&
			$result[1]['count_news'] == 1 &&
			$result[2]['title'] == 'Web Design' &&
			$result[2]['count_news'] == 1,
			$type.': count and sort on many-to-many relation'
		);

		// get all authors sorted by the amount of news they have written
		$author->reset();
		$author->countRel('news');
		$result = $author->find(null,array('order'=>'count_news DESC'))->castAll(0);

		$test->expect(
			$result[0]['name'] == 'Ridley Scott' &&
			$result[0]['count_news'] == 2 &&
			$result[1]['name'] == 'Johnny English' &&
			$result[1]['count_news'] == 1 &&
			$result[2]['name'] == 'James T. Kirk' &&
			$result[2]['count_news'] == null,
			$type.': count and sort on one-to-many relation'
		);

		$tag->reset();

		if ($type != 'sqlsrv2008') {
			$tag->countRel('news');
			$result=$tag->find(NULL,
				array('order'=>'count_news DESC, title DESC','limit'=>1,'offset'=>1))
				->castAll(0);

			$test->expect(
				$result[0]['title']=='Web Design' &&
				$result[0]['count_news']==1,
				$type.': apply limit and offset on aggregated collection'
			);
		} else {
			$test->expect(
				false,
				$type.': apply limit and offset on aggregated collection'
			);
		}

		$author->reset();
		$author->countRel('news');
		$author->has('news',array('text like ?','%Lorem%'));
		$result = $author->find()->castAll(0);

		$test->expect(
			count($result) == 1 &&
			$result[0]['name'] == 'Ridley Scott' &&
			$result[0]['count_news'] == 2 ,
			$type.': has-filter and 1:M relation counter'
		);


		$author->reset();
		$author->load();
		$id = $author->next()->_id;
		$tag->reset();
		$tag->countRel('news');
		$tag->has('news',array('author = ?',$id));
		$result = $tag->find(null,array('order'=>'count_news desc'))->castAll(0);

		$test->expect(
			count($result) == 2 &&
			$result[0]['title'] == 'Responsive' &&
			$result[0]['count_news'] == 3 &&
			$result[1]['title'] == 'Web Design' &&
			$result[1]['count_news'] == 1,
			$type.': has-filter and M:M relation counter'
		);


		$author = new AuthorModel();
		$author->has('friends', ['name LIKE ?','%Scott%']);
		$res = $author->find();
		$ids = $res->getAll('_id');
		$test->expect(
			$res && count($res) == 2 &&
			in_array($authorIDs[0],$ids) && in_array($authorIDs[2],$ids),
			$type.': has-filter on self-ref relation'
		);

		$author = new AuthorModel();
		$author->has('friends.news', ['title LIKE ?','%Interface%']);
		$res = $author->find();
		$test->expect(
			$res && ($ids = $res->getAll('_id')) && count($res) == 2 &&
			in_array($authorIDs[0],$ids) && in_array($authorIDs[2],$ids),
			$type.': has-filter, nested on self-ref relation'
		);

		$author = new AuthorModel();
		$author->has('friends', ['name LIKE ?','%Scott%']);
		$author->has('news', ['title LIKE ?','%CSS%']);
		$res = $author->find();
		$ids = $res ? $res->getAll('_id') : [];
		$test->expect(
			$res && count($res) == 1 &&
			in_array($authorIDs[0],$ids),
			$type.': multiple has-filter on self-ref relation'
		);

		$news->reset();
		$news->load(array('_id = ?',$newsIDs[0]));
		$time = date('Y-m-d H:i:s');
		$news->touch('created_at');
		$news->save();
		$news->reset();
		$news->load(array('_id = ?',$newsIDs[0]));
		$test->expect(
			substr($news->created_at,0,strlen($time)) == $time,
			$type.': update datetime field'
		);
		///////////////////////////////////
		return $test->results();
	}

}