<?php

use Saltwater\Server as S;

class RouteProviderTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		S::destroy();

		S::bootstrap('Saltwater\AppTest\AppTest');
	}

	protected function tearDown()
	{
		S::destroy();
	}

	/**
	 * @runInSeparateProcess
	 *
	 * @requires PHP 5.4
	 */
	public function testHalt()
	{
		if ( $GLOBALS['IS_HHVM'] ) { $this->markTestSkipped(); return; }

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		ob_start();

		S::$n->route->go();

		$this->assertEmpty(ob_get_clean());
	}

}
