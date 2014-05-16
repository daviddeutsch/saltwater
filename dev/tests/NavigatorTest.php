<?php

use Saltwater\Server as S;

class NavigatorTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		S::destroy();
	}

	public function testThingHandling()
	{
		S::init();

		$this->assertEquals( 1, S::$n->addThing('thing') );

		$this->assertEquals( 2, S::$n->addThing('thing2') );

		$this->assertEquals( 4, S::$n->addThing('thing3') );

		$this->assertEquals( 4, S::$n->addThing('thing3') );

		$this->assertTrue( S::$n->isThing('thing2') );

		$this->assertEquals( 1, S::$n->bitThing('thing') );
	}

	public function testRootModule()
	{
		S::init();

		$this->assertFalse( S::$n->addModule('\N\Existe\Pas') );

		$class = 'Saltwater\Root\Root';

		$this->assertTrue( S::$n->addModule($class, true) );

		$this->assertNull( S::$n->addModule($class) );

		$module = S::$n->getModule('root');

		$this->assertEquals( $class, get_class($module) );

		$this->assertTrue( S::$n->isThing('module.root') );

		$this->assertEquals( 1, S::$n->bitThing('module.root') );

		$this->assertEquals( 'root', $module->masterContext() );
	}

}
