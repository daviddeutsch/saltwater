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
		S::bootstrap();
	}

	public function testSaltHandling()
	{
		$this->assertEquals( 1, S::$n->registry->append('thing') );

		$this->assertEquals( 2, S::$n->registry->append('thing2') );

		$this->assertEquals( 4, S::$n->registry->append('thing3') );

		$this->assertEquals( 4, S::$n->registry->append('thing3') );

		$this->assertTrue( S::$n->registry->exists('thing2') );

		$this->assertEquals( 1, S::$n->registry->bit('thing') );
	}

	public function testWithRootModule()
	{
		$this->assertFalse( S::$n->modules->append('\N\Existe\Pas') );

		$class = 'Saltwater\Root\Root';

		$this->assertTrue( S::$n->modules->append($class, true) );

		$this->assertFalse( S::addModule($class) );

		$module = S::$n->modules->get('root');

		$this->assertEquals( $class, get_class($module) );

		$this->assertTrue( S::$n->registry->exists('module.root') );

		$this->assertEquals( 1, S::$n->registry->bit('module.root') );

		$this->assertEquals( 'root', $module->masterContext() );

		$this->assertEquals(
			array('root'),
			S::$n->modules->finder->modulesBySalt('provider.context')
		);

		$this->assertEquals(
			'Saltwater\Root\Context\Root',
			get_class(S::$n->modules->finder->masterContext())
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

		$this->assertEquals(
			'root',
			S::$n->modules->search(S::$n->modules->get('root'))
		);
	}

	/**
	 * @expectedException BadMethodCallException
	 */
	public function testFinderException()
	{
		S::$n->modules->nope();
	}

	/**
	 * @runInSeparateProcess
	 *
	 * @requires PHP 5.4
	 */
	public function testMissingProvider()
	{
		if ( $GLOBALS['IS_HHVM'] ) { $this->markTestSkipped(); return; }

		S::destroy();

		S::bootstrap('Saltwater\Root\Root');

		S::$n->nosuchprovider;

		$this->assertEquals(500, http_response_code());
	}
}
