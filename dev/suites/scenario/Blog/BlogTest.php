<?php

use Saltwater\Server as S;

class BlogTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();

		S::bootstrap('Saltwater\Blog\Blog');

		S::$n->db->nuke();
	}

	public static function tearDownAfterClass()
	{
		S::$n->db->nuke();

		S::destroy();
	}

	/**
	 * POST /article
	 */
	public function testArticlePost()
	{
		$this->expectOutputString('1');

		echo $this->request(
			'post',
			'article',
			array(
				'title' => 'Saltwater',
				'content' => 'Saltwater is water that contains a significant concentration of dissolved salts.'
			)
		);
	}

	public function testArticleGet()
	{
		$this->expectOutputString(
			'{"id":1,"title":"Saltwater","content":"Saltwater is water that contains a significant concentration of dissolved salts."}'
		);

		echo $this->request('get', 'article/1');
	}

	public function testPostSpeed()
	{
		$content = array(
			'title' => 'Saltwater',
			'content' => 'Saltwater is water that contains a significant concentration of dissolved salts.'
		);

		$results = array();
		for ( $i=0; $i<=1000; ++$i ) {
			$start = microtime(true);

			$this->request('post', 'article', $content);

			$results[] = $this->executionTime($start);
		}

		$average = $this->average($results);

		print_r("\n\n Average POST speed: " . round($average, 4) . "ms");

		if ( getenv('TRAVIS') ) $this->markTestSkipped();

		$this->assertLessThan( 10, $average );
	}

	public function testGetSpeed()
	{
		$results = array();
		for ( $i=1; $i<=1000; ++$i ) {
			$start = microtime(true);

			$this->request('get', 'article/' . $i);

			$results[] = $this->executionTime($start);
		}

		$average = $this->average($results);

		print_r("\n\n Average GET speed: " . round($average, 4) . "ms");

		if ( getenv('TRAVIS') ) $this->markTestSkipped();

		$this->assertLessThan( getenv('TRAVIS') ? 45 : 5, $average );
	}

	public function testCommentPostSpeed()
	{
		$content = array(
			'title' => 'Brine',
			'content' => 'Too salty!'
		);

		$results = array();
		for ( $i=0; $i<=1000; ++$i ) {
			$start = microtime(true);

			$this->request('post', 'comment', $content);

			$results[] = $this->executionTime($start);
		}

		$average = $this->average($results);

		print_r("\n\n Average POST (+entity) speed: " . round($average, 4) . "ms");

		if ( getenv('TRAVIS') ) $this->markTestSkipped();

		$this->assertLessThan( 20, $average );
	}

	public function testCommentGetSpeed()
	{
		$results = array();
		for ( $i=1; $i<=1000; ++$i ) {
			$start = microtime(true);

			$this->request('get', 'comment/' . $i);

			$results[] = $this->executionTime($start);
		}

		$average = $this->average($results);

		print_r("\n\n Average GET (+entity) speed: " . round($average, 4) . "ms");

		if ( getenv('TRAVIS') ) $this->markTestSkipped();

		$this->assertLessThan( 5, $average );
	}

	private function average($arr)
	{
		return array_sum($arr) / count($arr);
	}

	private function executionTime( $start )
	{
		$time = microtime(true);

		return round( ($time - $start) * 1000, 2 );
	}

	private function request( $method, $path, $input=null )
	{
		$GLOBALS['METHOD'] = strtoupper($method);

		$GLOBALS['PATH'] = $path;

		$input = $input ? $this->convertInputToJSON($input) : null;

		$GLOBALS['mock_input'] = $input;

		return $this->route()->go();
	}

	/**
	 * @return \Saltwater\Test\Provider\Route
	 */
	private function route()
	{
		return S::$n->route('blog');
	}

	private function convertInputToJSON( $input )
	{
		return json_encode( $this->recursiveConvertArrayToObject($input) );
	}

	private function recursiveConvertArrayToObject( $input )
	{
		$output = new stdClass();

		if ( is_array($input) ) {
			foreach ( $input as $k => $v ) {
				$output->$k = $this->recursiveConvertArrayToObject($v);
			}
		} else {
			$output = $input;
		}

		return $output;
	}
}
