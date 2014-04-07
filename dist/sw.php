<?php

/**
 * Saltwater
 *
 * @license GNU GPL v3
 *
 * copyright (c) 2014 David Deutsch
 */
class Saltwater_Server
{
	public static $config;

	public static $subject;

	public static $session;

	public static $context = array();

	/**
	 * @var Saltwater_Router
	 */
	public static $route;

	/**
	 * @var Saltwater_Logger
	 */
	public static $log;

	/**
	 * @var RedBean_Instance
	 */
	public static $r;

	public static function init( $context, $uri=null )
	{
		self::$config = $context->config;

		self::db();

		self::$log = new Logger();

		self::pushContext('root', $context);

		self::$route = new Router($uri);

		$context->verifyRoute();
	}

	public static function route()
	{
		self::$route->go();
	}

	public static function getContext( $name, $parent=null )
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

	public static function findContext( $name )
	{
		$class = 'Saltwater_Context_' . ucfirst($name);

		if ( !class_exists($class) ) return false;

		return $class;
	}

	public static function pushContext( $handle, $context )
	{
		self::$context = array_merge(
			array( $handle => $context ),
			self::$context
		);
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
			self::$r = new RedBean_Instance();
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
			new Saltwater_ModelFormatter
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

class S extends Saltwater_Server {}

class Saltwater_Router
{
	public $http;

	public $uri;

	public $chain = array();

	public function __construct( $uri=null )
	{
		if ( empty($uri) ) {
			$this->uri = $this->getURI();
		} else {
			$this->uri = $uri;
		}

		$this->http = strtolower($_SERVER['REQUEST_METHOD']);

		$this->explode( S::$context[0], $this->http, explode('/', $this->uri) );
	}

	protected function getURI()
	{
		$path = $_SERVER['SCRIPT_NAME'];

		if ( strpos($_SERVER['REQUEST_URI'], $path) === false ) {
			$path = str_replace( '\\', '', dirname($path) );
		}

		$path = substr_replace( $_SERVER['REQUEST_URI'], '', 0, strlen($path) );

		if ( isset($_SERVER['QUERY_STRING']) ) {
			$path = str_replace('?' . $_SERVER['QUERY_STRING'], '', $path);
		}

		$path = preg_replace('`[^a-z0-9/._-]+`', '', strtolower($path));

		if ( strpos($path, '.zip') ) {
			$path = str_replace('.zip', '', $path);
		}

		return $path;
	}

	public function go()
	{
		$input = @file_get_contents('php://input');

		if ( !$input ) $input = '';

		$result = null;

		$length = count($this->chain) - 1;

		foreach ( $this->chain as $i => $call ) {
			$call->context->pushData($result);

			$service = $call->context->getService($call->class, $result);

			// TODO: Middleware for individual Services

			if ( ($i == $length) && !empty($input) ) {
				$result = $service->call($call, json_decode($input));
			} else {
				$result = $service->call($call);
			}
		}

		if ( is_object($result) || is_array($result) ) {
			S::returnJSON($result);
		} else {
			S::returnEcho($result);
		}
	}

	protected function explode( $context, $cmd, $path )
	{
		$root = array_shift($path);

		// This is for simple commands upon an established service
		if ( empty($path) ) {
			$this->push($context, $cmd, $root);

			return;
		}

		$c = S::findContext($root);

		if ( $c ) {
			// This is for switching into a child context
			$context = new $c($context);

			S::pushContext($context);

			$root = array_shift($path);
		}

		$next = array_shift($path);

		// Either push a call on the last service or a new one into the chain
		if ( empty($path) ) {
			$this->push($context, $cmd, $root, $next);
		} else {
			$this->push($context, 'get', $root, $next);

			$this->explode($context, $cmd, $path);
		}
	}

	protected function push( $context, $cmd, $service, $path=null )
	{
		$method = $service;

		if ( !empty($path) && !is_numeric($path) ) {
			$method = $path;

			$path = null;
		}

		$class = $context->findService($service);

		if ( strpos($method, '-') ) {
			$method = $cmd . str_replace(' ', '',
					ucwords( str_replace('-', ' ', $method) )
				);
		} else {
			$method = $cmd . ucfirst($method);
		}

		if ( !method_exists($class, $method) ) {
			$plain = true;
		} else {
			$plain = $method == $service;
		}

		$this->chain[] = (object) array(
			'context' => $context,
			'http' => $cmd,
			'service' => ucfirst($service),
			'class' => $class,
			'method' => $method,
			'plain' => $plain,
			'path' => $path
		);
	}
}

/**
 * Describes log levels
 *
 * See https://github.com/php-fig/fig-standards
 */
abstract class Psr_AbstractLogger
{
	public function emergency($message, array $context = array())
	{
		$this->log('emergency', $message, $context);
	}

	public function alert($message, array $context = array())
	{
		$this->log('alert', $message, $context);
	}

	public function critical($message, array $context = array())
	{
		$this->log('critical', $message, $context);
	}

	public function error($message, array $context = array())
	{
		$this->log('error', $message, $context);
	}

	public function warning($message, array $context = array())
	{
		$this->log('warning', $message, $context);
	}

	public function notice($message, array $context = array())
	{
		$this->log('notice', $message, $context);
	}

	public function info($message, array $context = array())
	{
		$this->log('info', $message, $context);
	}

	public function debug($message, array $context = array())
	{
		$this->log('debug', $message, $context);
	}

	public function log($level, $message, $context) {}
}

class Saltwater_Logger extends Psr_AbstractLogger
{
	public function log( $level, $message, array $context=array() )
	{
		S::$r->_(
			'log',
			array_merge(
				array(
					'created' => S::$r->isoDateTime(),
					'level' => $level,
					'message' => $message
				),
				$context
			),
			true
		);
	}
}

class Saltwater_ModelFormatter implements RedBean_IModelFormatter
{
	public function formatModel( $name )
	{
		return S::formatModel($name);
	}
}

class Saltwater_Context_Context
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
		$class = $this->namespace . '_Service_' . ucfirst($name);

		if ( class_exists($class) ) return $class;

		if ( in_array($name, $this->services) ) {
			return 'Saltwater\Service\Rest';
		} elseif ( !empty($this->parent) ) {
			return $this->parent->findService($name);
		} elseif ( class_exists('Saltwater_Service_' . ucfirst($name)) ) {
			return 'Saltwater_Service_' . ucfirst($name);
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
		return $this->namespace .'_Models_'
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

	public function getInfo()
	{
		return null;
	}
}

class Saltwater_Context_Root extends Saltwater_Context_Context
{
	public $root = true;
}

class Saltwater_Model_Model extends RedBean_PipelineModel {}

class Saltwater_Model_AssociationModel extends RedBean_PipelineAssociationModel {}

class Saltwater_Log extends Saltwater_Model {}

class Saltwater_Service_Service
{
	protected $context;

	public function __construct( $context )
	{
		$this->context = $context;
	}

	public function is_callable( $method )
	{
		return method_exists($this, $method);
	}

	public function call( $call, $data=null )
	{
		$func = array($this, $call->method);

		if ( empty( $call->path ) && empty( $data ) ) {
			return call_user_func($func);
		}

		if ( empty( $call->path ) ) {
			return call_user_func($func, $data);
		} else {
			return call_user_func($func, $call->path, $data);
		}
	}
}

class Saltwater_Service_Rest extends Saltwater_Service_Service
{
	public function call( $call, $data=null )
	{
		if ( $this->is_callable($call->method) ) {
			return parent::call($call, $data);
		}

		return $this->restCall($call, $data);
	}

	protected function restCall( $call, $data=null )
	{
		$path = strtolower( str_replace($call->http, '', $call->method) );

		if ( is_numeric($call->path) ) {
			$path .= '/' . $call->path;
		}

		return $this->callPath($call->http, $path, $data);
	}

	protected function callPath( $http, $path, $data=null )
	{
		$rest = $this->restHandler();

		return $rest->handleRESTRequest($http, $path, $data);
	}

	protected function restHandler()
	{
		return new \RedBean_Plugin_BeanCan($this->context->getDB());
	}
}

class Saltwater_Service_Info extends Saltwater_Service_Service
{
	public function getInfo()
	{
		return $this->context->getInfo();
	}
}
