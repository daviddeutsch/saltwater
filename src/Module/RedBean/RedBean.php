<?php

namespace Saltwater\RedBean;

use Saltwater\Thing\Module;

class RedBean extends Module
{
	public static $name = 'redbean';

	public static $namespace = 'Saltwater\RedBean';

	protected $require = array(
		'module' => array('Saltwater\Root\Root')
	);

	protected $provide = array(
		'provider' => array('entity', 'db', 'log'),
		'context' => array('redbean'),
		'service' => array('rest'),
		'entity' => array('log')
	);
}
