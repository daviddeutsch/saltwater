<?php

use Saltwater\Server as S;
use Saltwater\Utils as U;

class ProviderTest extends \PHPUnit_Framework_TestCase
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

    public function testDummyProvider()
    {
        // ~Shamelessly~ Unapologetically increasing coverage
        $this->assertEquals(
            new stdClass(),
            S::$n->dummy
        );
    }
}
