<?php

use Saltwater\Server as S;
use Saltwater\Utils as U;

class ServiceTest extends \PHPUnit_Framework_TestCase
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
		S::init('Saltwater\TestService\TestService');

		$context = S::$n->context->get('test-service');

		$lacking = S::$n->service->get('lacking', $context);

		$call = $this->makeCall($context, 'get', 'lacking', 'true');

		$this->assertTrue( $lacking->call($call) );

		$call = $this->makeCall($context, 'get', 'lacking', 'null');

		$this->assertNull( $lacking->call($call) );
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
