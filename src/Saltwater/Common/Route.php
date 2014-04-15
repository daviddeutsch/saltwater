<?php

namespace Saltwater\Common;

/**
 * A route converts a uri path and http method
 * into a chain of Contexts and Services
 */
abstract class Route implements Provider
{
	public $http;

	public $uri;

	public $chain = array();

	public function go() {}
}
