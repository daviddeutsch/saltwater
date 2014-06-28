<?php

use Saltwater\Server as S;

class RedBeanProviderTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();
	}

	protected function tearDown()
	{
		S::destroy();
	}

	public function testServiceProvider()
	{
		S::init('Saltwater\Root\Root');

		S::$n->modules->append('Saltwater\RedBean\RedBean', true);

		$this->assertEquals(
			'Saltwater\RedBean\Provider\Entity',
			get_class(S::$n->entity)
		);

		$this->assertEquals(
			'Saltwater\RedBean\Provider\Log',
			get_class(S::$n->log)
		);
	}
}
