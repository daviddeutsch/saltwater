<?php

use Saltwater\Utils as U;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
	public function testSnakeToCamelCase()
	{
		$this->assertEquals(
			'ThisIsATest',
			U::snakeToCamelCase('this_is_a_test')
		);

		$this->assertEquals(
			'Short',
			U::snakeToCamelCase('short')
		);

		$this->assertEquals(
			'VeryVeryVeryVeryLONG',
			U::snakeToCamelCase('very_very_very_very_LONG')
		);
	}
}
