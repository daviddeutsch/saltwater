<?php

namespace Saltwater\Test\Provider;

use Saltwater\App\Provider\Response as AppResponse;

class Response extends AppResponse
{
	public static function getProvider() { return new Response; }

	/**
	 * Redirect the client to a different URL
	 *
	 * @param $url
	 */
	public function redirect( $url )
	{
		return "Location: " . $url;
	}

	/**
	 * Output data as JSON
	 *
	 * @param object|array $data
	 */
	public function json( $data )
	{
		return json_encode( $this->prepareOutput($data) );
	}

	/**
	 * Output data as plain text
	 *
	 * @param string $data
	 */
	public function plain( $data )
	{
		return $data;
	}
}
