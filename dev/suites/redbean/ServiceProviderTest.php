<?php

use Saltwater\Server as S;

class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        S::destroy();

        S::bootstrap('Saltwater\Test\Test');
    }

    protected function tearDown()
    {
        S::destroy();
    }

    public function testServiceProvider()
    {
        $this->assertEquals(
            'RedBean_Instance',
            get_class(S::$n->db)
        );
    }
}
