<?php

use Saltwater\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
	public function testBasicFunctionality()
	{
		$registry = new Registry;

		// Adding yields ID 1
		$this->assertEquals( 1, $registry->append('test') );

		// Repetition yields the same ID
		$this->assertEquals( 1, $registry->append('test') );

		// Checking the ID another way
		$this->assertEquals( 1, $registry->bit('test') );

		// Testing whether test exists
		$this->assertTrue( $registry->exists('test') );

		// Adding a few more items
		$this->assertEquals( 2, $registry->append('test2') );
		$this->assertEquals( 4, $registry->append('test3') );
		$this->assertEquals( 8, $registry->append('test4') );

		$this->assertEquals( 8, $registry->bit('test4') );

		$this->assertFalse( $registry->exists('test5') );
	}
}
