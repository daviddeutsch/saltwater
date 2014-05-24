<?php

use Saltwater\Server as S;

class LoggingTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		S::destroy();

		S::init('Saltwater\Blog\Blog');

		S::$n->db->nuke();
	}

	public static function tearDownAfterClass()
	{
		S::$n->db->nuke();
	}

	public function testLogging()
	{
		$log = S::$n->log;

		$this->assertEquals(
			'RedBean_Instance',
			get_class(S::$n->db)
		);

		$this->assertEquals(
			'log',
			S::entity('log')
		);

		$this->assertEquals( 1, $log->debug('test')->id );

		$this->assertEquals( 2, $log->info('test')->id );

		$this->assertEquals( 3, $log->notice('test')->id );

		$this->assertEquals( 4, $log->warning('test')->id );

		$this->assertEquals( 5, $log->error('test')->id );

		$this->assertEquals( 6, $log->critical('test')->id );

		$this->assertEquals( 7, $log->alert('test')->id );

		$this->assertEquals( 8, $log->emergency('test')->id );
	}
}
