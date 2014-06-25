<?php

namespace Saltwater\App\Common;

use Saltwater\Salt\Provider;

/**
 * A route converts a uri path and http method
 * into a chain of Contexts and Services
 */
abstract class Route extends Provider
{
	public $http;

	public $uri;

	public $chain = array();

	public function go() {}
}
