<?php

use Saltwater\Utils as U;

class UtilsTest extends PHPUnit_Framework_TestCase

{
	public function testSnakeToCamelCase()
	{
		$this->assertEquals(
			U::snakeToCamelCase('this_is_a_test'),
			'ThisIsATest'
		);

		$this->assertEquals(
			U::snakeToCamelCase('short'),
			'Short'
		);

		$this->assertEquals(
			U::snakeToCamelCase('very_very_very_very_LONG'),
			'VeryVeryVeryVeryLong'
		);
	}
}
