<?php

namespace MangroveServer\Service;

class Rest extends AbstractService
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
		$root = strtolower( str_replace($call->http, '', $call->method) );

		if ( is_numeric($call->path) ) {
			return $this->callPath(
				$call->http,
				$root . '/' . $call->path,
				$data
			);
		} else {
			return $this->callPath(
				$call->http,
				$root,
				$data
			);
		}
	}

	protected function callPath( $http, $path, $data=null )
	{
		$rest = $this->restHandler();

		$return = $rest->handleRESTRequest($http, $path, $data);

		if ( $http != 'get' ) return $return;

		if ( is_array($return) ) {
			foreach ( $return as $k => $v ) {
				$return[$k] = $this->convertNumeric($v);
			}
		} else {
			$return = $this->convertNumeric($return);
		}

		return $return;
	}

	protected function convertNumeric( $object )
	{
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

	protected function restHandler()
	{
		return new \RedBean_Plugin_BeanCan($this->context->getDB());
	}
}
