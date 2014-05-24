<?php

use Saltwater\Server as S;

class BlogTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();

		S::init('Saltwater\Blog\Blog');
	}

	public static function tearDownAfterClass()
	{
		//S::$n->db->nuke();
	}

	public function setUp()
	{

	}

	public function testArticlePost()
	{
		// POST /article
		$this->assertEquals(
			1,
			$this->request(
				'post',
				'/article',
				array(
					'title' => 'My first Blog Post',
					'content' => 'Hey there, first time posting'
				)
			)
		);

		$this->assertEquals(
			1,
			$this->request(
				'get',
				'/article',
				array(
					'title' => 'My first Blog Post',
					'content' => 'Hey there, first time posting'
				)
			)
		);

	}

	private function request( $method, $path, $input=null )
	{
		$_SERVER['REQUEST_METHOD'] = $method;

		$_SERVER['REQUEST_URI'] = $path;

		$GLOBALS['input'] = $input ? $this->convertInputToJSON($input) : null;

		ob_start();

		S::$n->route->go();

		return ob_get_clean();
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
