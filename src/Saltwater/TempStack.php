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
	private $master = '';

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

	/**
	 * Set the master module by name
	 *
	 * @param string $name
	 */
	public function setMaster( $name )
	{
		if ( empty($name) || ($name == $this->master) ) return;

		$this->master = $name;

		$this->pushStack($name);
	}

	public function isMaster( $name )
	{
		return $name == $this->master;
	}

	/**
	 * Push a module name onto the stack, establishing later hierarchy for calls
	 *
	 * @param string $name
	 */
	private function pushStack( $name )
	{
		if ( empty($this) ) $this[] = $this->root;

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

		if ( $master == (count((array) $this) - 1) ) return false;

		return $this[$master+1];
	}
}
