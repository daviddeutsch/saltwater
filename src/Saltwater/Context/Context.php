<?php

namespace Saltwater\Context;

use Saltwater\Server as S;

class Context
{
	public $root = false;

	public $namespace;

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

		$namespace = explode('\\', __NAMESPACE__ );

		unset($namespace[count($namespace)]);

		$this->namespace = implode('\\', $namespace);
	}

	public function pushData( $data )
	{

	}

	public function findService( $name )
	{
		$class = $this->namespace . '\Service\\' . ucfirst($name);

		if ( class_exists($class) ) return $class;

		if ( in_array($name, $this->services) ) {
			$class = $this->namespace . '\Service\Rest';

			if ( !class_exists($class) ) {
				$class = $this->namespace . '\Service\Rest';
			}

		} else {
			return '';
		}

		return $class;

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

	public function db()
	{
		if ( empty(self::$r) ) {
			self::$r = new \RedBean_Instance();
		}

		$cfg = self::$config->database;

		if ( empty(self::$r->toolboxes) ) {
			self::$r->setup(
				'mysql:host=' . $cfg->host . ';' . 'dbname=' . $cfg->name,
				$cfg->user,
				$cfg->password
			);

			self::$r->setupPipeline();
		}

		if ( !isset(self::$r->toolboxes[$cfg->name]) ) {
			self::$r->addDatabase(
				$cfg->name,
				'mysql:host=' . $cfg->host . ';' . 'dbname=' . $cfg->name,
				$cfg->user,
				$cfg->password
			);
		}

		self::$r->selectDatabase($cfg->name);

		self::$r->useWriterCache(true);
	}

	public function getInfo()
	{
		return null;
	}
}
