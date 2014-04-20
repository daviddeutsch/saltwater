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

	/**
	 * Redirect the client to a different URL
	 *
	 * @param $url
	 */
	public function redirect( $url )
	{
		header('HTTP/1.1 307 Temporary Redirect');

		header("Location: " . $url);

		exit;
	}

	/**
	 * Output data as JSON
	 *
	 * @param object|array $data
	 */
	public function json( $data )
	{
		header('HTTP/1.0 200 OK');

		header('Content-type: application/json');

		echo json_encode( self::prepareOutput($data) );

		exit;
	}

	/**
	 * Output data as a plain text or JSON, depending on its type
	 *
	 * @param object|array|string $data
	 */
	public function response( $data )
	{
		if ( is_object($data) || is_array($data) ) {
			$this->json($data);
		} else {
			$this->plain($data);
		}
	}

	/**
	 * Output data as plain text
	 *
	 * @param string $data
	 */
	public function plain( $data )
	{
		header('HTTP/1.0 200 OK');

		echo $data;

		exit;
	}

	/**
	 * Ensure we are encoding numeric properties as numbers, not strings
	 *
	 * @param object|array $input
	 *
	 * @return array
	 */
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

	/**
	 * Convert all numeric properties of an object into floats or integers
	 *
	 * @param object $object
	 *
	 * @return object
	 */
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
