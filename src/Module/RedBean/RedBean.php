<?php

namespace Saltwater\RedBean;

use Saltwater\Salt\Module;

class RedBean extends Module
{
	public static $name = 'redbean';

	protected $require = array(
		'module' => array('Saltwater\Root\Root')
	);

	protected $provide = array(
		'provider' => array('entity', 'db', 'log'),
		'service' => array('rest')
	);
}
