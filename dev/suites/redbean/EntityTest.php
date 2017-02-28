<?php

use Saltwater\Server as S;
use Saltwater\Utils as U;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        S::destroy();

        S::bootstrap('Saltwater\RedBeanTest\RedBeanTest');
    }

    protected function tearDown()
    {
        S::destroy();
    }

    public function testEntity()
    {
        $this->assertEquals(
            'Saltwater\RedBeanTest\Entity\Test',
            S::$n->entity->get('test')
        );

        $this->assertEquals(
            'Saltwater\RedBean\Salt\Entity',
            S::$n->entity->get('classless')
        );
    }
}
