<?php

namespace Saltwater\Thing;

use Saltwater\Utils as U;

/**
 * A module is a static object that can register providers and declare contexts
 *
 * @package Saltwater\Thing
 */
class Module
{
	protected $providers = array();

	protected $contexts = array();

	protected $services = array();

	protected $entities = array();

	protected $instances = array(
		'providers' => array(),
		'contexts' => array()
	);

	public function context( $name, $parent=null )
	{
		if ( isset($this->instances['context'][$name]) ) {
			return self::$context[$name];
		}

		$context = self::findContext($name);

		if ( !$context ) return false;

		self::$context[$name] = new $context($parent);

		return self::$context[$name];
	}

	public function findContext()
	{
		foreach ( self::$context as $context ) {
			$class = 'Saltwater\\' . U::dashedToCamelCase($context);

			if ( class_exists($class) ) return $class;
		}

		return false;
	}

	public function providers()
	{
		if ( empty($this->providers) ) return array();

		return $this->providers;
	}

	public function provider( $name )
	{

	}

	public function contexts()
	{
		if ( empty($this->contexts) ) return array();

		return $this->contexts;
	}

	public function services()
	{
		if ( empty($this->services) ) return array();

		return $this->services;
	}

	public function entities()
	{
		if ( empty($this->entities) ) return array();

		return $this->entities;
	}
}
