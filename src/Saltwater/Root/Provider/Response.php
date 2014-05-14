<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Thing\Provider;

class Response extends Provider
{
	public static function getProvider() { return new Response; }

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

		header( 'X-Execution-Time: ' . $this->executionTime() . 'ms' );

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

		header( 'X-Execution-Time: ' . $this->executionTime() . 'ms' );

		echo $data;

		exit;
	}

	private function executionTime()
	{
		return round((microtime(true) - S::$start)*1000, 2);
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
			return $this->outputArray($input);
		} else {
			return $this->output($input);
		}
	}

	private function output( $input )
	{
		return $this->convertNumeric($input);
	}

	private function outputArray( $input )
	{
		$return = array();
		foreach ( $input as $k => $v ) {
			$return[$k] = $this->output($v);
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

			$object->$k = $this->stringToNum($v);
		}

		return $object;
	}

	private function stringToNum( $value )
	{
		if ( strpos($value, '.') !== false ) {
			return (float) $value;
		} else {
			return (int) $value;
		}
	}
}
