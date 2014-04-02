<?php

namespace Saltwater;

class Server
{
	public static $config;

	public static $subject;

	public static $session;

	/**
	 * @var Router
	 */
	public static $route;

	/**
	 * @var \RedBean_Instance
	 */
	public static $r;

	/**
	 * @var Logger
	 */
	public static $log;

	public static function init( $context )
	{
		self::db();

		self::$log = new Logger();

		self::$route = new Router($context);
	}

	public static function route()
	{
		self::$route->go();
	}

	public static function db()
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

		self::$r->redbean->beanhelper->setModelFormatter(new ModelFormatter);
	}

	public static function returnRedirect( $url )
	{
		header('HTTP/1.1 307 Temporary Redirect');

		header("Location: " . $url);

		exit;
	}

	public static function returnJSON( $data )
	{
		header('HTTP/1.0 200 OK');

		header('Content-type: application/json');

		echo json_encode($data);

		exit;
	}

	public static function returnEcho( $data )
	{
		header('HTTP/1.0 200 OK');

		echo $data;

		exit;
	}

	public static function halt( $code, $message )
	{
		header("HTTP/1.1 " . $code . " " . $message);

		exit;
	}
}
