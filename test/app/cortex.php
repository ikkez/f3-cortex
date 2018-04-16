<?php

namespace App;

class Cortex extends Controller
{
	function get()
	{
		$f3 = \Base::instance();
		$f3->set('QUIET', false);

		$dbs = array(
//			'sql-mysql' => new \DB\SQL('mysql:host=localhost;port=3306;dbname=fatfree', 'fatfree', ''),
			'sql-sqlite' => new \DB\SQL('sqlite:data/sqlite.db'),
//			'sql-pgsql' => new \DB\SQL('pgsql:host=localhost;dbname=fatfree', 'fatfree', 'fatfree'),
//			'jig' => new \DB\Jig('data/',\DB\Jig::FORMAT_JSON,true),
//			'mongo' => new \DB\Mongo('mongodb://localhost:27017', 'testdb'),
//			'sqlsrv2008' => new \DB\SQL('sqlsrv:SERVER=WIN7\SQLEXPRESS2008;Database=fatfree','sa', 'fatfree'),
//			'sqlsrv2012' => new \DB\SQL('sqlsrv:SERVER=WIN7\SQLEXPRESS2012;Database=fatfree','sa', 'fatfree'),
//			'sqlsrv2014' => new \DB\SQL('sqlsrv:SERVER=WIN7\SQLEXPRESS2014;Database=fatfree','sa', 'fatfree'),
		);
		$results = array();

//		$dbs['mongo']->log(false);

		// Test Syntax
		foreach ($dbs as $type => $db) {
			$test = new \Test_Syntax();
			$results = array_merge((array) $results, (array) $test->run($db, $type));
		}

		// Test Relations
		foreach ($dbs as $type => $db) {
			$f3->set('DB',$db);
			$test = new \Test_Relation();
			$results = array_merge((array) $results, (array) $test->run($db, $type));
		}

		// Test Filter
		foreach ($dbs as $type => $db) {
			$f3->set('DB',$db);
			$test = new \Test_Filter();
			$results = array_merge((array) $results, (array) $test->run($db, $type));
		}

//		 Further Common Tests
		if (isset($dbs['sql-mysql'])) {
			$test = new \Test_Common();
			$f3->set('DB', $dbs['sql-mysql']);
			$results = array_merge((array) $results, (array) $test->run());
		}

		$f3->set('results', $results);
	}


}