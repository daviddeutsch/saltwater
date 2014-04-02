<?php

namespace Saltwater\Context;

use Saltwater\Server as S;

class Context
{
	public $root = false;

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

	public function getServiceClass( $service )
	{
		$class = 'MangroveServer\Service\\' . ucfirst($service);

		if ( !class_exists($class) ) {
			if ( in_array($service, $this->services) ) {
				$class = 'MangroveServer\Service\Rest';
			} else {
				return '';
			}
		}

		return $class;

	}

	public function getService( $service, $result )
	{
		return new $service($this, $result);
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
