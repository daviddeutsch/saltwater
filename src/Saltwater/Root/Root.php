<?php

namespace Saltwater\Root;

use Saltwater\Thing\Module;

class Root extends Module
{
	public $namespace = 'Saltwater\Root';

	protected $providers = array(
		'entity', 'context', 'service',
		'db', 'log', 'route', 'response'
	);

	protected $contexts = array('root');

	protected $services = array('rest', 'info');

	protected $entities = array('log');
}
