<?php

use Saltwater\Server as S;

class ServerTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		S::destroy();
	}

	public function testCache()
	{
		$path = __DIR__ . '/cache/cache.cache';

		S::init('Saltwater\Root\Root', $path);

		$navigator = clone S::$n;

		S::destroy();

		S::init('Saltwater\Root\Root', $path);

		$this->assertEquals($navigator, S::$n);

		unlink($path);

		rmdir(__DIR__.'/cache');
	}

	public function testModuleActions()
	{
		S::init('Saltwater\Root\Root');

		S::addModules(
			array('Saltwater\RedBean\RedBean', 'Saltwater\App\App')
		);

		$this->assertEquals(
			'Saltwater\RedBean\RedBean',
			get_class( S::$n->getModule('redbean') )
		);

		$this->assertEquals(
			'Saltwater\RedBean\Thing\Entity',
			S::entity('log')
		);
	}
}
