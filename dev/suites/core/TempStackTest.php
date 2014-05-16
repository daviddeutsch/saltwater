<?php

use Saltwater\TempStack;

class TempStackTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var TempStack
	 */
	public $stack;

	public function setUp()
	{
		$this->stack = new TempStack;
	}

	public function testNewStack()
	{
		$this->assertEquals( 'root', $this->stack->getRoot() );

		$this->assertTrue( $this->stack->isRoot('root') );

		$this->assertEquals( 'root', $this->stack->getMaster() );

		$this->assertTrue( $this->stack->isMaster('root') );
	}

	public function testRootManipulation()
	{
		$this->stack->setRoot('tree');

		$this->assertEquals( 'tree', $this->stack->getRoot() );

		$this->assertTrue( $this->stack->isRoot('tree') );
	}

	public function testNewMaster()
	{
		$this->stack->setMaster('node1');

		$this->assertEquals( 'node1', $this->stack->getMaster() );

		$this->assertTrue( $this->stack->isMaster('node1') );
	}

	public function testMultiMasterAddition()
	{
		$this->stack->setMaster('node1');
		$this->stack->setMaster('node2');
		$this->stack->setMaster('node3');
		$this->stack->setMaster('node4');

		$this->assertTrue( $this->stack->isMaster('node4') );
	}

	public function testPrecedence()
	{
		$this->stack->setMaster('node1');
		$this->stack->setMaster('node2');

		$this->assertEquals(
			array(
				'node2', 'node1', 'root'
			),
			$this->stack->modulePrecedence()
		);

		$this->stack->setMaster('node1');

		$this->assertEquals(
			array(
				'node1', 'root'
			),
			$this->stack->modulePrecedence()
		);

		$this->assertEquals( 'node2', $this->stack->advanceMaster() );

		$this->assertEquals(
			array(
				'node2', 'node1', 'root'
			),
			$this->stack->modulePrecedence()
		);

		$this->assertFalse( $this->stack->advanceMaster() );
	}
}
