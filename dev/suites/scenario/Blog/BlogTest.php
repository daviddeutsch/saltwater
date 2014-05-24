<?php

use Saltwater\Server as S;

class BlogTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::init('Saltwater\Blog');
	}

	public static function tearDownAfterClass()
	{
		S::$n->db->nuke();
	}

	public function setUp()
	{
		S::destroy();
	}

	public function testArticlePost()
	{
		// POST /article
		$this->request(
			'post',
			'/article',
			array(
				'title' => 'My first Blog Post',
				'content' => 'Hey there, first time posting'
			)
		);
	}

	private function request( $method, $path, $input=null )
	{
		$_SERVER['REQUEST_METHOD'] = $method;

		if ( $input ) {
			file_put_contents("php://input", $this->convertInputToJSON($input));
		}

		S::$n->route->go();
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
