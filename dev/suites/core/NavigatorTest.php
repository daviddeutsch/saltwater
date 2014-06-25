<?php

use Saltwater\Server as S;

class NavigatorTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();
	}

	protected function tearDown()
	{
		S::destroy();
	}

	protected function setUp()
	{
		S::init();
	}

	public function testSaltHandling()
	{
		$this->assertEquals( 1, S::$n->addSalt('thing') );

		$this->assertEquals( 2, S::$n->addSalt('thing2') );

		$this->assertEquals( 4, S::$n->addSalt('thing3') );

		$this->assertEquals( 4, S::$n->addSalt('thing3') );

		$this->assertTrue( S::$n->isSalt('thing2') );

		$this->assertEquals( 1, S::$n->bitSalt('thing') );
	}

	public function testWithRootModule()
	{
		$this->assertFalse( S::$n->addModule('\N\Existe\Pas') );

		$class = 'Saltwater\Root\Root';

		$this->assertTrue( S::$n->addModule($class, true) );

		$this->assertNull( S::addModule($class) );

		$module = S::$n->getModule('root');

		$this->assertEquals( $class, get_class($module) );

		$this->assertTrue( S::$n->isSalt('module.root') );

		$this->assertEquals( 1, S::$n->bitSalt('module.root') );

		$this->assertEquals( 'root', $module->masterContext() );

		$this->assertEquals(
			'root',
			S::$n->moduleBySalt('provider.context')
		);

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
			'Saltwater\Root\Provider\Service',
			get_class(S::$n->service())
		);

		$path = __DIR__ . '/cache/cache.cache';

		$copy = clone S::$n;

		$this->assertNotFalse( S::$n->storeCache($path) );

		S::$n->loadCache($path);

		$this->assertEquals($copy, S::$n);

		unlink($path);

		rmdir(__DIR__.'/cache');
	}
}
