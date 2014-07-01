<?php

use Saltwater\Server as S;

class ResponseProviderTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		S::destroy();

		S::init('Saltwater\AppTest\AppTest');
	}

	protected function tearDown()
	{
		S::destroy();
	}

	/**
	 * @runInSeparateProcess
	 *
	 * @requires PHP 5.4
	 */
	public function testRedirect()
	{
		if (
			(getenv('TRAVIS_PHP_VERSION') == 'hhvm')
			|| (getenv('TRAVIS_PHP_VERSION') == 'hhvm-nightly')
		) {
			$this->markTestSkipped(); return;
		}

		$url = 'https://github.com/daviddeutsch/saltwater';

		S::$n->response->redirect($url);

		$this->assertEquals(307, http_response_code());

		$this->assertContains( 'Location: ' . $url, xdebug_get_headers() );
	}

	/**
	 * @runInSeparateProcess
	 *
	 * @requires PHP 5.4
	 */
	public function testJSON()
	{
		if (
			(getenv('TRAVIS_PHP_VERSION') == 'hhvm')
			|| (getenv('TRAVIS_PHP_VERSION') == 'hhvm-nightly')
		) {
			$this->markTestSkipped(); return;
		}

		$test = (object) array('one' => 'two');

		$this->assertEquals(
			'{"one":"two"}',
			S::$n->response->json($test)
		);

		$this->assertEquals(200, http_response_code());

		$this->assertContains(
			'Content-type: application/json',
			xdebug_get_headers()
		);
	}

	/**
	 * @runInSeparateProcess
	 *
	 * @requires PHP 5.4
	 */
	public function testPlain()
	{
		if (
			(getenv('TRAVIS_PHP_VERSION') == 'hhvm')
			|| (getenv('TRAVIS_PHP_VERSION') == 'hhvm-nightly')
		) {
			$this->markTestSkipped(); return;
		}

		$test = "test";

		$this->assertEquals(
			'test',
			S::$n->response->plain($test)
		);

		$this->assertEquals(200, http_response_code());
	}

	/**
	 * @runInSeparateProcess
	 *
	 * @requires PHP 5.4
	 */
	public function testGenericToJSON()
	{
		if (
			(getenv('TRAVIS_PHP_VERSION') == 'hhvm')
			|| (getenv('TRAVIS_PHP_VERSION') == 'hhvm-nightly')
		) {
			$this->markTestSkipped(); return;
		}

		$test = (object) array('one' => 'two');

		$this->assertEquals(
			'{"one":"two"}',
			S::$n->response->response($test)
		);

		$this->assertEquals(200, http_response_code());

		$this->assertContains(
			'Content-type: application/json',
			xdebug_get_headers()
		);
	}


	/**
	 * @runInSeparateProcess
	 *
	 * @requires PHP 5.4
	 */
	public function testGenericToJSONArray()
	{
		if (
			(getenv('TRAVIS_PHP_VERSION') == 'hhvm')
			|| (getenv('TRAVIS_PHP_VERSION') == 'hhvm-nightly')
		) {
			$this->markTestSkipped(); return;
		}

		$test = array(
			(object) array('one' => 'two'),
			(object) array('one' => 'two')
		);

		$this->assertEquals(
			'[{"one":"two"},{"one":"two"}]',
			S::$n->response->response($test)
		);

		$this->assertEquals(200, http_response_code());

		$this->assertContains(
			'Content-type: application/json',
			xdebug_get_headers()
		);
	}

	/**
	 * @runInSeparateProcess
	 *
	 * @requires PHP 5.4
	 */
	public function testGenericToPlain()
	{
		if (
			(getenv('TRAVIS_PHP_VERSION') == 'hhvm')
			|| (getenv('TRAVIS_PHP_VERSION') == 'hhvm-nightly')
		) {
			$this->markTestSkipped(); return;
		}

		$test = "test";

		$this->assertEquals(
			'test',
			S::$n->response->response($test)
		);

		$this->assertEquals(200, http_response_code());
	}
}
