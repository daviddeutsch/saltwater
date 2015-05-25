<?php

namespace Saltwater\Bus\Water;

class Call
{
	public $context;

	public $service;

	public $method;

	public $path;

	public $meta;

	public function __construct( $context, $service, $method, $path, $meta )
	{
		$this->context = $context;

		$this->service = $service;

		$this->method = $method;

		$this->path = $path;

		$this->meta = $meta;
	}
}
