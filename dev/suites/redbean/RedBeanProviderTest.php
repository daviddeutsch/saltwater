<?php

use Saltwater\Server as S;

class RedBeanDbTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();

		S::init('Saltwater\Test\Test');
	}

	protected function tearDown()
	{
		S::destroy();
	}

	public function testServiceProvider()
	{
		$this->assertEquals(
			'RedBean_Instance',
			get_class(S::$n->db)
		);
	}
}
