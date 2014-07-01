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
		S::$n->db->selectDatabase('overload');

		S::$n->db->nuke();

		S::$n->db->selectDatabase('default');

		S::$n->db->nuke();

		S::destroy();
	}

	public function testSecondDb()
	{
		S::addModule('Saltwater\Overload\Overload');

		$this->assertArrayHasKey('overload', S::$n->db->toolboxes);
	}
}
