<?php

namespace Saltwater\Root;

use Saltwater\Thing\Module;

class Root extends Module
{
	public $namespace = 'Saltwater\Root';

	protected $provide = array(
		'provider' => array(
			'entity', 'context', 'service', 'db', 'log', 'route', 'response'
		),
		'context' => array('root'),
		'service' => array('rest', 'info'),
		'entity' => array('log')
	);
}
