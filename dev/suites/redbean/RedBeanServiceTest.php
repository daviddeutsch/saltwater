<?php

use Saltwater\Server as S;
use Saltwater\Utils as U;

class RedBeanServiceTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();

		S::init('Saltwater\RedBeanTest\RedBeanTest');
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

		$this->assertEquals( array(), $test->call($call) );
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
