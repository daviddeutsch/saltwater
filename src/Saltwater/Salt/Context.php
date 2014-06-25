<?php

namespace Saltwater\Salt;

/**
 * A context accepts and returns services, decides provider priority(?)
 *
 * In a path, contexts provide hierarchy and encapsulation
 */
class Context
{
	public $namespace;

	public $parent;

	public $module;

	public $data;

	public $services = array();

	public function __construct( $parent=null, $module=null )
	{
		if ( !is_null($parent) ) $this->parent = $parent;

		if ( !is_null($module) ) $this->module = $module;
	}

	public function pushData( $data )
	{
		$this->data = $data;
	}

	public function getInfo()
	{
		return null;
	}
}
