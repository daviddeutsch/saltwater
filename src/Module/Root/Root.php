<?php

namespace Saltwater\Root;

use Saltwater\Thing\Module;

class Root extends Module
{
	public static $name = 'root';

	public static $namespace = 'Saltwater\Root';

	protected $provide = array(
		'provider' => array('context', 'service'),
		'context' => array('root'),
		'service' => array('info')
	);
}
