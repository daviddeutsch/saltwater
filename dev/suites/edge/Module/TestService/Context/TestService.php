<?php

namespace Saltwater\TestService\Context;

use Saltwater\Thing\Context;

class TestService extends Context
{
	public $namespace = 'Saltwater\TestService';

	public $services = array('lacking');
}
