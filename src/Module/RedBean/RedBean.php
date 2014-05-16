<?php

namespace Saltwater\RedBean;

use Saltwater\Thing\Module;

class RedBean extends Module
{
	public static $name = 'redbean';

	public static $namespace = 'Saltwater\RedBean';

	protected $provide = array(
		'provider' => array('entity', 'db', 'log'),
		'service' => array('rest'),
		'entity' => array('log')
	);
}
