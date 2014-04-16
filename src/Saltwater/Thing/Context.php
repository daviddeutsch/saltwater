<?php

namespace Saltwater\Thing;

/**
 * A context accepts and returns services, decides provider priority(?)
 *
 * In a path, contexts provide hierarchy and encapsulation
 */
class Context
{
	public $namespace;

	public $parent;

	public $data;

	public $services = array();

	public function __construct( $parent=null )
	{
		if ( !is_null($parent) ) $this->parent = $parent;
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
