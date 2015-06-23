<?php

use Saltwater\Server as S;

class ModuleFinderTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();

		S::bootstrap('Saltwater\Test\Test');
	}

	protected function tearDown()
	{
		S::destroy();
	}

	public function testFinderOutliers()
	{
		$bit = S::$n->registry->bit('provider.response');

		$this->assertEquals(
			array('app', 'test'),
			S::$n->modules->finder->getSaltModules($bit)
		);

		$this->assertEmpty( S::$n->modules->finder->getSaltModules(1234) );
	}
}
