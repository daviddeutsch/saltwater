<?php

namespace Saltwater;

class Registry
{
	/**
	 * @var string[] array of Saltwater\Thing(s)
	 */
	private $things = array();

	/**
	 * Return true if the input is a registered thing
	 *
	 * @param string $name in the form "type.name"
	 *
	 * @return bool
	 */
	public function isThing( $name )
	{
		return in_array($name, $this->things) !== false;
	}

	/**
	 * Return the bitmask integer of a thing
	 *
	 * @param string $name in the form "type.name"
	 *
	 * @return bool|int
	 */
	public function bitThing( $name )
	{
		return array_search($name, $this->things);
	}

	/**
	 * Register a thing and return its bitmask integer
	 * @param $name
	 *
	 * @return number
	 */
	public function addThing( $name )
	{
		if ( $id = $this->bitThing($name) ) return $id;

		$id = pow( 2, count($this->things) );

		$this->things[$id] = $name;

		return $id;
	}

}
