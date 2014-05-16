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

	public function testWithRootModule()
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

		$this->assertEquals(
			'Saltwater\Root\Context\Root',
			get_class(S::$n->masterContext())
		);

		// Testing different ways of calling in providers
		$this->assertEquals(
			'Saltwater\Root\Provider\Context',
			get_class(S::$n->provider('context'))
		);

		$this->assertEquals(
			'Saltwater\Root\Provider\Service',
			get_class(S::$n->service)
		);

		$this->assertEquals(
			'Saltwater\Root\Provider\Entity',
			get_class(S::$n->entity())
		);

		$path = __DIR__ . '/cache/cache.cache';

		$copy = clone S::$n;

		$this->assertNotFalse( S::$n->storeCache($path) );

		S::$n->loadCache($path);

		$this->assertEquals( $copy, S::$n );

		unlink($path);

		rmdir(__DIR__.'/cache');
	}

}
