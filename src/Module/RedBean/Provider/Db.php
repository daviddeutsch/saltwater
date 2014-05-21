<?php

namespace Saltwater\RedBean\Provider;

use Saltwater\Server as S;
use Saltwater\Thing\Provider;

class Db extends Provider
{
	/**
	 * @var \RedBean_Instance
	 */
	private static $r;

	protected static function makeDB()
	{
		if ( empty(self::$r) ) self::$r = new \RedBean_Instance();

		$cfg = S::$n->config(self::$caller)->database;

		if ( !isset($cfg->type) ) $cfg->type = 'mysql';

		self::setupDB($cfg);

		self::addDB($cfg);

		self::configureDB($cfg);
	}

	private static function setupDB( $cfg )
	{
		if ( !empty(self::$r->toolboxes) ) return;

		self::$r->setup(
			$cfg->type . ':host=' . $cfg->host . ';' . 'dbname=' . $cfg->name,
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
			$cfg->type . ':host=' . $cfg->host . ';' . 'dbname=' . $cfg->name,
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
			'Saltwater\Server::entity'
		);

		self::$r->useWriterCache(true);
	}

	/**
	 * @return \RedBean_Instance
	 */
	public static function getProvider()
	{
		if ( empty(self::$r) ) {
			self::makeDB();
		}

		return self::$r;
	}
}
