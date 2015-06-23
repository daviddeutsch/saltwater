<?php

use Saltwater\Server as S;

class RootProviderTest extends \PHPUnit_Framework_TestCase
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
		S::bootstrap('Saltwater\Root\Root');

		$context = S::$n->context->get('root');

		$this->assertEquals(
			'Saltwater\Root\Context\Root',
			get_class($context)
		);

		$service = S::$n->service->get('info', $context);

		$this->assertEquals(
			'Saltwater\Root\Service\Info',
			get_class($service)
		);

		$this->assertNull($service->getInfo());
	}
}
