<?php

namespace Saltwater\RedBean;

use Saltwater\Thing\Module;

class Root extends Module
{
	public static $name = 'root';

	public static $namespace = 'Saltwater\Root';

	protected $provide = array(
		'provider' => array('entity', 'db', 'log'),
		'service' => array('rest'),
		'entity' => array('log')
	);
}
