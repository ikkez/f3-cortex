<?php

namespace App;

class Cortex_Benchmark extends Controller
{

    private
        $roundTime = 0,
        $f3,
        $test,
        $currentType;

    private function getTime()
    {
        $time = microtime(TRUE) - $this->f3->get('timer') - $this->roundTime;
        $this->roundTime = microtime(TRUE) - $this->f3->get('timer');
        return $this->currentType.' [ '.sprintf('%.3f', $time).'s ]';
    }


    function get()
    {
        $this->f3 = \Base::instance();
        $this->f3->set('AUTOLOAD', $this->f3->get('AUTOLOAD').';app/cortex/');
        $this->f3->set('QUIET', false);

        $this->test = new \Test();

        $dbs = array(
            'sql' => new \DB\SQL('mysql:host=localhost;port=3306;dbname=fatfree', 'fatfree', ''),
//            'jig' => new \DB\Jig('data/'),
//            'mongo' => new \DB\Mongo('mongodb://localhost:27017', 'testdb')
        );

        foreach ($dbs as $type => $db) {

            $this->f3->set('DB',$db);
            $this->currentType = $type;

            $this->roundTime = microtime(TRUE) - \Base::instance()->get('timer');

            \AuthorModel::setdown();
            \TagModel::setdown();
            \NewsModel::setdown();

            \AuthorModel::setup();
            \TagModel::setup();
            \NewsModel::setup();

            $this->insert_tags();
            $this->insert_authors();
            $this->insert_news();
            $this->insert_news();

            $news = new \NewsModel();
            // reset timer
            $this->getTime();

//            $result = $news->find(null,array('limit'=>5000));
//            $this->test->message($this->getTime()." hydrate 5000 records");

//            unset($news,$result);
//            $news = new \NewsModel();

//            $result = $news->find(null,array('limit' => 1000));
//            $result[0]->author->name;
//            $this->test->message($this->getTime()." hydrate 1000 records with 1 relation fixed size");

//			unset($result);
            $result = $news->find(null, array('limit' => 1000));
            $result[0]->tags2[0];
            $this->test->message($this->getTime()." hydrate 1000 records with 1 relation random size");

//            $this->test->message($this->getTime()." hydrate 1000 records with 2 relations random size");

//            $result = $news->find('author = 11');
//            $this->test->message($this->getTime()." find news from a specific author(11): ".count($result).' news found');

//            $result = $news->has('author',array('mail LIKE ?','%.co.uk%'))->find();
//            $this->test->message($this->getTime()." find news with has condition on author: ".count($result).' news found');

    //        var_dump($this->f3->get('DB')->log());

        }

        $this->f3->set('results', $this->test->results());
    }

    function insert_tags()
    {
        $tags = new \TagModel();
        $newTags = array(
            'CSS','HTML','JavaScript','Techniques','Typography',
            'Inspiration','Business','User Experience','E-Commerce','Design Pattern'
        );

        foreach($newTags as $record) {
            $tags->title = $record;
            $tags->save();
            $tags->reset();
        }

        $this->test->message($this->getTime()." imported 10 Tags ");
    }

    function insert_authors()
    {
        $author = new \AuthorModel();

		$author_data = json_decode($this->f3->read('app/cortex/bench/user.json'),true);

		foreach($author_data as $record) {
            $author->copyfrom($record);
            $author->save();
            $author->reset();
        }

        $this->test->message($this->getTime()." imported 20 Users");
    }

    function insert_news()
    {
        $news = new \NewsModel();
        $news_data = json_decode($this->f3->read('app/cortex/bench/news.json'),true);
        for($i=0;$i<5;$i++)
            foreach($news_data as $record) {
                $news->title = $record['title'];
                $news->text = $record['text'];
                $news->author = $record['author'];
                $news->tags2 = $record['tags2'];
                $news->save();
                $news->reset();
            }
        $this->test->message($this->getTime()." imported 500 News");
    }

}