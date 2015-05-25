<?php

namespace Saltwater\Salt;

/**
 * Context
 *
 * @package Saltwater\Bus
 *
 * A context accepts and returns services, decides provider priority through
 * module stacking.
 *
 * In a path, contexts provide hierarchy and encapsulation
 */
class Context
{
	/** @var string */
	public $namespace;

	/** @var Context */
	public $parent;

	/** @var Module */
	public $module;

	/** @var mixed */
	public $data;

	/** @var array */
	public $services = array();

	/**
	 * @param Context|null $parent
	 * @param Module|null  $module
	 */
	public function __construct( $parent=null, $module=null )
	{
		if ( !is_null($parent) ) $this->parent = $parent;

		if ( !is_null($module) ) $this->module = $module;
	}

	/**
	 * @param mixed $data
	 */
	public function pushData( $data )
	{
		$this->data = $data;
	}

	/**
	 * @return null
	 */
	public function getInfo()
	{
		return null;
	}
}
