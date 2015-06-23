<?php

use Saltwater\Server as S;
use Saltwater\Utils as U;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();

		S::bootstrap('Saltwater\TestService\TestService');
	}

	public static function tearDownAfterClass()
	{
		S::destroy();
	}

	public function testServiceProvider()
	{
		$context = S::$n->context->get('test-service');

		$this->assertEquals(
			'Saltwater\RedBean\Service\Rest',
			get_class(S::$n->service->get('nonexistent', $context))
		);

		$lacking = S::$n->service->get('lacking', $context);

		$call = $this->makeCall($context, 'get', 'lacking', 'true');

		$this->assertTrue( $lacking->call($call) );

		$call = $this->makeCall($context, 'get', 'lacking', 'null');

		$this->assertNull( $lacking->call($call) );

		$extensive = S::$n->service->get('extensive', $context);

		$test = array('one' => 'two');

		$path = 'providers';

		$call = $this->makeCall($context, 'get', 'extensive', $path);

		$return = $extensive->call($call, $test);

		$this->assertEquals($test, $return[0]);

		$this->assertNull($return[1]);

		$this->assertEquals(
			'Saltwater\Root\Provider\Context',
			get_class($return[2])
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
