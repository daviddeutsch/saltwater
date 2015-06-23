<?php

use Saltwater\Server as S;
use Saltwater\Utils as U;

class TestServiceTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		S::destroy();

		S::bootstrap('Saltwater\RedBeanTest\RedBeanTest');
	}

	protected function tearDown()
	{
		S::destroy();
	}

	public function testTestService()
	{
		$context = S::$n->context->get('red-bean-test');

		$test = S::$n->service->get('test', $context);

		$this->assertTrue( $test->isCallable('getCustom') );

		$call = $this->makeCall($context, 'get', 'test', 'custom');

		$this->assertEquals( 'itWorked', $test->call($call) );

		$this->assertTrue( $test->isCallable('getTest') );

		$call = $this->makeCall($context, 'get', 'test', 'test');

		$this->assertEmpty( $test->call($call) );
	}

	public function testProviderInjection()
	{
		$context = S::$n->context->get('red-bean-test');

		$test = S::$n->service->get('test', $context);

		$this->assertTrue( $test->isCallable('getProvider') );

		$call = $this->makeCall($context, 'get', 'test', 'provider');

		$entity = $test->call($call);

		$this->assertEquals(
			'Saltwater\RedBeanTest\Entity\Test',
			$entity->get('test')
		);
	}

	private function makeCall( $context, $cmd, $service, $path=null )
	{
		$method = $service;

		if ( !empty($path) && !is_numeric($path) ) {
			$method = $path;

			$path = null;
		}

		return (object) array(
			'context'  => $context,
			'http'     => $cmd,
			'service'  => $service,
			'method'   => $method,
			'function' => $cmd . U::dashedToCamelCase($method),
			'path'     => $path
		);
	}
}
