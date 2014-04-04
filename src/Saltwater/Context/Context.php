<?php

namespace Saltwater\Context;

use Saltwater\Server as S;

class Context
{
	public $root = false;

	public $namespace = 'Saltwater';

	public $parent;

	public $data;

	public $services = array();

	public function __construct( $parent=null )
	{
		if ( is_null($parent) ) {
			$this->root = true;
		} else {
			$this->parent = $parent;
		}
	}

	public function pushData( $data )
	{

	}

	public function findService( $name )
	{
		$class = $this->namespace . '\Service\\' . ucfirst($name);

		if ( class_exists($class) ) return $class;

		if ( in_array($name, $this->services) ) {
			return 'Saltwater\Service\Rest';
		} else {
			return '';
		}
	}

	public function getService( $service, $result )
	{
		return new $service($this, $result);
	}

	public function formatModel( $name )
	{
		return $this->namespace .'\Models\\'
			. str_replace(' ', '',
				ucwords( str_replace('_', ' ', $name) )
			);
	}

	public function getDB()
	{
		if ( $this->root ) {
			return S::$r;
		} else {
			return $this->parent->getDB();
		}
	}

	public function getInfo()
	{
		return null;
	}
}
