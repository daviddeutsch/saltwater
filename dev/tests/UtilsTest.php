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

	public function testDashedToCamelCase()
	{
		$this->assertEquals(
			'Class',
			U::dashedToCamelCase('class')
		);

		$this->assertEquals(
			'MyApp',
			U::dashedToCamelCase('my-app')
		);

		$this->assertEquals(
			'VeryVeryVeryVeryLONG',
			U::dashedToCamelCase('very-very-very-very-LONG')
		);
	}

	public function testCamelCaseSpaced()
	{
		$this->assertEquals(
			'ThisIsATest',
			U::CamelCaseSpaced('This is a Test')
		);
	}

	public function testCamelTodashed()
	{
		$this->assertEquals(
			'this-is-a-test',
			U::CamelTodashed('ThisIsATest')
		);

		$this->assertEquals(
			'short',
			U::CamelTodashed('Short')
		);

		$this->assertEquals(
			'very-very-very-very-long',
			U::CamelTodashed('VeryVeryVeryVeryLong')
		);
	}

	public function testNamespacedClassToDashed()
	{
		$this->assertEquals(
			'test',
			U::namespacedClassToDashed('This\Is\A\Test')
		);

		$this->assertEquals(
			'short',
			U::namespacedClassToDashed('Short')
		);

		$this->assertEquals(
			'long',
			U::namespacedClassToDashed('Very\Very\Very\Very\Long')
		);
	}

	public function testClassName()
	{
		$this->assertEquals(
			'Class',
			U::className('class')
		);

		$this->assertEquals(
			'Namespaced\Class',
			U::className('namespaced', 'class')
		);

		$this->assertEquals(
			'Namespaced\Class\Here',
			U::className('namespaced', 'class', 'here')
		);
	}

	public function testClassExplode()
	{
		$name = 'Saltwater\Thing\Module';

		$this->assertEquals(
			array('Saltwater', 'Thing', 'Module'),
			U::explodeClass($name)
		);

		$this->assertEquals(
			array('Saltwater', 'Thing', 'Module'),
			U::explodeClass(new $name)
		);
	}

	public function testJSON()
	{
		$path = __DIR__ . '/test.json';

		$content = array(
			'test' => 'property',
			'array' => array('also', 1, 'property')
		);

		$this->assertNotFalse(
			U::storeJSON($path, $content)
		);

		$this->assertEquals(
			(object) $content,
			U::getJSON($path)
		);

		$this->assertEquals(
			$content,
			U::getJSON($path, true)
		);

		unlink($path);
	}
}
