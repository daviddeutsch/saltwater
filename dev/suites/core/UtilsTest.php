<?php

use Saltwater\Utils as U;
use Saltwater\Server as S;

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
            U::camelCaseSpaced('This is a Test')
        );
    }

    public function testCamelTodashed()
    {
        $this->assertEquals(
            'this-is-a-test',
            U::camelTodashed('ThisIsATest')
        );

        $this->assertEquals(
            'short',
            U::camelTodashed('Short')
        );

        $this->assertEquals(
            'very-very-very-very-long',
            U::camelTodashed('VeryVeryVeryVeryLong')
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

        $this->assertEquals(
            'Namespaced\ClassSnake',
            U::className('namespaced', 'class_snake')
        );
    }

    public function testClassExplode()
    {
        $name = 'Saltwater\Salt\Module';

        $this->assertEquals(
            array('Saltwater', 'Salt', 'Module'),
            U::explodeClass($name)
        );

        $this->assertEquals(
            array('Saltwater', 'Salt', 'Module'),
            U::explodeClass(new $name)
        );
    }

    public function testJSON()
    {
        $path = __DIR__ . '/test.json';

        $content = array(
            'test'  => 'property',
            'array' => array('also', 1, 'property')
        );

        $this->assertNotFalse(
            U::storeJSON($path, $content)
        );

        $store = S::$env['gt54'];

        S::$env['gt54'] = false;

        $this->assertNotFalse(
            U::storeJSON($path, $content)
        );

        S::$env['gt54'] = $store;

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
