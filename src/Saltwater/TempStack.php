<?php

namespace Saltwater;

class TempStack extends \ArrayObject
{
	/**
	 * @var string
	 */
	private $root = 'root';

	/**
	 * @var string
	 */
	private $master = 'root';

	public function __construct()
	{
		$this[] = $this->root;
	}

	/**
	 * Set the root module by name
	 *
	 * @param string $name
	 */
	public function setRoot( $name )
	{
		if ( empty($name) || ($name == $this->root) ) return;

		$this->root = $name;
	}

	public function isRoot( $name )
	{
		return $name == $this->root;
	}

	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * Set the master module by name
	 *
	 * @param string $name
	 *
	 * @return string previous master
	 */
	public function setMaster( $name )
	{
		if ( empty($name) || ($name == $this->master) ) return $this->master;

		$previous_master = $this->master;

		$this->master = $name;

		$this->pushStack($name);

		return $previous_master;
	}

	public function isMaster( $name )
	{
		return $name == $this->master;
	}

	public function getMaster()
	{
		return $this->master;
	}

	/**
	 * Push a module name onto the stack, establishing later hierarchy for calls
	 *
	 * @param string $name
	 */
	private function pushStack( $name )
	{
		if ( in_array($name, (array) $this) ) return;

		$this[] = $name;
	}

	public function modulePrecedence()
	{
		$return = array();
		foreach ( (array) $this as $module ) {
			array_unshift($return, $module);

			if ( $module == $this->master ) break;
		}

		return $return;
	}

	public function advanceMaster()
	{
		$master = array_search($this->master, (array) $this);

		if ( $master == ($this->count() - 1) ) return false;

		$this->master = $this[$master+1];

		return $this->master;
	}
}
