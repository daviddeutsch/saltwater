<?php

use Saltwater\Server as S;

class MultiDbTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		S::destroy();

		S::init('Saltwater\Test\Test');
	}

	protected function tearDown()
	{
		S::destroy();
	}

	public function testSecondDb()
	{
		S::addModule('Saltwater\Overload\Overload');

		$this->assertContains('overload', S::$n->db->toolboxes);
	}
}
