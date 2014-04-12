<?php

namespace Saltwater;

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
		$this->data = $data;
	}

	public function findService( $name )
	{
		$class = $this->namespace . '\Service\\' . ucfirst($name);

		if ( class_exists($class) ) return $class;

		$root = 'Saltwater\Module\Root';

		if ( in_array($name, $this->services) ) {
			return $root.'\Service\Rest';
		} elseif ( !empty($this->parent) ) {
			return $this->parent->findService($name);
		} elseif ( class_exists($root.'\Service\\' . ucfirst($name)) ) {
			return $root.'\Service\\' . ucfirst($name);
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
		return $this->namespace .'\Entity\\'
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
