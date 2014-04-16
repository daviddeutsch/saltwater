<?php

namespace Saltwater\Root\Provider;

use Saltwater\Thing\Provider;

class Response extends Provider
{
	protected function __construct() {}

	public static function get()
	{
		return new Response();
	}

	public function redirect( $url )
	{
		header('HTTP/1.1 307 Temporary Redirect');

		header("Location: " . $url);

		exit;
	}

	public function json( $data )
	{
		header('HTTP/1.0 200 OK');

		header('Content-type: application/json');

		echo json_encode( self::prepareOutput($data) );

		exit;
	}

	public function response( $data )
	{
		if ( is_object($data) || is_array($data) ) {
			$this->json($data);
		} else {
			$this->plain($data);
		}
	}

	public function plain( $data )
	{
		header('HTTP/1.0 200 OK');

		echo $data;

		exit;
	}

	private function prepareOutput( $input )
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

	protected function convertNumeric( $object )
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
