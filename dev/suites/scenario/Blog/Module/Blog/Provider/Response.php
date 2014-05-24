<?php

namespace Saltwater\Blog\Provider;

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
		echo "Location: " . $url;
	}

	/**
	 * Output data as JSON
	 *
	 * @param object|array $data
	 */
	public function json( $data )
	{
		echo json_encode( $this->prepareOutput($data) );
	}

	/**
	 * Output data as plain text
	 *
	 * @param string $data
	 */
	public function plain( $data )
	{
		echo $data;
	}
}
