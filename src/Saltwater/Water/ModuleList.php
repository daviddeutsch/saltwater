<?php

namespace Saltwater\Water;

use Saltwater\Salt\Module;

class ModuleList
{
	/**
	 * @var Module[]
	 */
	private $list = array();

	public function __construct( $list )
	{
		$this->list = $list;
	}

	/**
	 * Return a list of Modules providing a Salt
	 *
	 * @param int $bit
	 *
	 * @return Module[]|string[]
	 */
	public function filterByBit( $bit )
	{
		$return = array();
		foreach ( $this->list as $module ) {
			if ( $module->has($bit) ) $return[] = $module::getName();
		}

		return $return;
	}

	/**
	 * Return one Module providing a Salt
	 *
	 * @param int $bit
	 *
	 * @return Module|string
	 */
	public function filterOneBit( $bit )
	{
		foreach ( $this->list as $module ) {
			if ( $module->has($bit) ) return $module::getName();
		}

		return false;
	}
}
