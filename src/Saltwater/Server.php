<?php

namespace Saltwater;

class Server
{
	public static $config;

	public static $subject;

	public static $session;

	public static $context = array();

	/**
	 * @var Router
	 */
	public static $route;

	/**
	 * @var Logger
	 */
	public static $log;

	/**
	 * @var \RedBean_Instance
	 */
	public static $r;

	public static function init( $context, $uri=null )
	{
		self::$config = $context->config;

		self::db();

		self::$log = new Logger();

		self::pushContext($context);

		self::$route = new Router($uri);

		$context->verifyRoute();
	}

	public static function route()
	{
		self::$route->go();
	}

	public static function pushContext( $context )
	{
		array_unshift(self::$context, $context);
	}

	public static function formatModel( $name )
	{
		foreach ( self::$context as $context ) {
			$model = $context->formatModel($name);

			if ( !empty($model) ) {
				return $model;
			}
		}

		return $name;
	}

	public static function db()
	{
		if ( empty(self::$r) ) {
			self::$r = new \RedBean_Instance();
		}

		$cfg = self::$config->database;

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

		self::$r->redbean->beanhelper->setModelFormatter(new ModelFormatter);

		self::$r->useWriterCache(true);
	}

	public static function findContext( $name )
	{
		$class = 'Saltwater\Context\\' . ucfirst($name);

		if ( !class_exists($class) ) return false;

		return $class;
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

		echo json_encode( self::prepareOutput($data) );

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

	private static function prepareOutput( $input )
	{
		if ( is_array($input) ) {
			$return = array();
			foreach ( $input as $k => $v ) {
				$return[$k] = self::convertNumeric($v);
			}
		} else {
			$return = self::convertNumeric($input);
		}

		return $return;
	}

	protected static function convertNumeric( $object )
	{
		if ( $object instanceof \RedBean_OODBBean ) {
			$object = $object->export();
		}

		foreach ( get_object_vars($object) as $k => $v ) {
			if ( !is_numeric($v) ) continue;

			if ( strpos($v, '.') !== false ) {
				$object->$k = (float) $v;
			} else {
				$object->$k = (int) $v;
			}
		}

		return $object;
	}
}
