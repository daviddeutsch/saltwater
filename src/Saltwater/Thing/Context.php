<?php

namespace Saltwater\Thing;

use Saltwater\Server as S;
use Saltwater\Utils as U;

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

		$root = 'Saltwater\Root';

		if ( in_array($name, $this->services) ) {
			return $root . '\Service\Rest';
		} elseif ( !empty($this->parent) ) {
			return $this->parent->findService($name);
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
		$name = U::snakeToCamelCase($name);

		$self = $this->namespace . '\Entity\\' . $name;

		if ( class_exists($self) ) {
			return $self;
		} elseif ( !empty($this->parent) ) {
			return $this->parent->formatModel($name);
		} else {
			return $name;
		}
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
