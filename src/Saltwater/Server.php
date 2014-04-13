<?php

namespace Saltwater;

class Server
{
	/**
	 * @var array|Module
	 */
	private static $modules = array();

	/**
	 * @var array|Context
	 */
	public static $context = array();

	/**
	 * @var Router
	 */
	public static $route;

	/**
	 * @var \Saltwater\Common\Log
	 */
	public static $log;

	/**
	 * @var \Saltwater\Common\Config
	 */
	public static $config;

	/**
	 * @var \Saltwater\Common\Subject
	 */
	public static $subject;

	/**
	 * @var \Saltwater\Common\Session
	 */
	public static $session;

	/**
	 * @var \RedBean_Instance
	 */
	public static $r;

	public static function init( $modules, $uri=null )
	{
		self::$config = $context->config;

		self::db();

		self::$log = new Logger();

		self::pushContext('root', $context);

		self::$route = new Router($uri);

		$context->verifyRoute();

		self::$db      = self::provider('db');
		self::$config  = self::provider('config');
		self::$log     = self::provider('log');
		self::$subject = self::provider('subject');
		self::$session = self::provider('session');
	}

	public static function route()
	{
		self::$route->go();
	}

	/**
	 * Return a context from our stack of modules
	 *
	 * @param string        $name
	 * @param Thing\Context $parent
	 *
	 * @return Thing\Context
	 */
	public static function context( $name, $parent=null )
	{
		if ( isset(self::$context[$name]) ) {
			return self::$context[$name];
		}

		$c = self::findContext($name);

		if ( !$c ) return false;

		$context = new $c($parent);

		self::pushContext($name, $context);

		return $context;
	}

	public static function service( $context, $name )
	{
		return self::provide( 'service', $name, array($context) );
	}

	public static function provider( $name )
	{
		return self::provide( 'provider', $name );
	}

	public static function provide( $type, $name, $input=null )
	{

	}

	public static function findContext( $name )
	{
		foreach ( self::$modules as $module ) {
			$context = $module::findContext($name);

			if ( !empty($context) ) return $context;
		}

		return false;
	}

	public static function pushContext( $handle, $context )
	{
		self::$context = array_merge(
			array( $handle => $context ),
			self::$context
		);
	}

	public static function formatModel( $name, $bean=null )
	{
		return self::provide('entity', $name, array($bean));
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

		if ( !empty($cfg->prefix) ) {
			self::$r->prefix($cfg->prefix);
		}

		self::$r->redbean->beanhelper->setModelFormatter(
			'Saltwater\Server::formatModel'
		);

		self::$r->useWriterCache(true);
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
