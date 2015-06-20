<?php

namespace Saltwater\RedBean\Provider;

use Saltwater\Server as S;
use Saltwater\Salt\Provider;

class Db extends Provider
{
	/**
	 * @var \RedBean_Instance
	 */
	private static $r;

	/**
	 * @return \RedBean_Instance
	 */
	public function __construct()
	{
		if ( empty(self::$r) ) {
			self::makeDB();
		}

		return self::$r;
	}

	protected static function makeDB()
	{
		if ( empty(self::$r) ) self::$r = new \RedBean_Instance();

		$cfg = S::$n->config->database;

		if ( !isset($cfg->type) ) $cfg->type = 'mysql';

		self::setupDB($cfg);

		self::addDB($cfg);

		self::configureDB($cfg);
	}

	private static function setupDB( $cfg )
	{
		if ( !empty(self::$r->toolboxes) ) return;

		self::$r->setup(
			self::cfgToDSN($cfg),
			$cfg->user,
			$cfg->password
		);

		self::$r->setupPipeline();
	}

	private static function addDB( $cfg )
	{
		if ( isset(self::$r->toolboxes[$cfg->name]) ) return;

		self::$r->addDatabase(
			$cfg->name,
			self::cfgToDSN($cfg),
			$cfg->user,
			$cfg->password
		);
	}

	private static function configureDB( $cfg )
	{
		self::$r->selectDatabase($cfg->name);

		if ( !empty($cfg->prefix) ) {
			self::$r->prefix($cfg->prefix);
		}

		self::$r->redbean->beanhelper->setModelFormatter(
			'Saltwater\RedBean\Provider\Db::entity'
		);

		self::$r->useWriterCache(true);
	}

	/**
	 * Return an Entity class name from the EntityProvider
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function entity( $name )
	{
		return S::$n->entity->get($name);
	}

	private static function cfgToDSN( $cfg )
	{
		if ( isset($cfg->dsn) ) return $cfg->dsn;

		return self::makeDSN($cfg->type, $cfg->host, $cfg->name);
	}

	private static function makeDSN( $type, $host, $name )
	{
		return $type . ':host=' . $host . ';' . 'dbname=' . $name;
	}
}
