<?php

namespace Saltwater\Root\Service;

use Saltwater\Thing\Service;

class Rest extends Service
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