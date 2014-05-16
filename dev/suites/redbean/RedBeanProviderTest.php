<?php

use Saltwater\Server as S;

class RedBeanProviderTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		S::destroy();
	}

	public function testServiceProvider()
	{
		S::init('Saltwater\Root\Root');

		S::$n->addModule('Saltwater\RedBean\RedBean', true);

		$this->assertEquals(
			'Saltwater\RedBean\Provider\Entity',
			get_class(S::$n->entity)
		);

		$this->assertEquals(
			'Saltwater\RedBean\Provider\Log',
			get_class(S::$n->log)
		);

		/*$this->assertEquals(
			'\RedBean_Instance',
			get_class(S::$n->db)
		);*/
	}
}
