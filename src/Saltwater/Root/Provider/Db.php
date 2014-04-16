<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Thing\Provider;

class Db extends Provider
{
	private static $r;

	protected function __construct()
	{
		$cfg = S::$n->config->database;

		if ( empty(self::$r) ) {
			self::$r = new \RedBean_Instance();
		}

		if ( isset($cfg->type) ) {
			$type = $cfg->type;
		} else {
			$type = 'mysql';
		}

		if ( empty(self::$r->toolboxes) ) {
			self::$r->setup(
				$type . ':host=' . $cfg->host . ';' . 'dbname=' . $cfg->name,
				$cfg->user,
				$cfg->password
			);

			self::$r->setupPipeline();
		}

		if ( !isset(self::$r->toolboxes[$cfg->name]) ) {
			self::$r->addDatabase(
				$cfg->name,
				$type . ':host=' . $cfg->host . ';' . 'dbname=' . $cfg->name,
				$cfg->user,
				$cfg->password
			);
		}

		self::$r->selectDatabase($cfg->name);

		if ( !empty($cfg->prefix) ) {
			self::$r->prefix($cfg->prefix);
		}

		self::$r->redbean->beanhelper->setModelFormatter(
			'Saltwater\Server::entity'
		);

		self::$r->useWriterCache(true);
	}

	public static function get()
	{
		self::__construct();

		return self::$r;
	}
}
