<?php

namespace Saltwater\Root;

use Saltwater\Thing\Module;

class Root extends Module
{
	protected $providers = array(
		'db', 'entity', 'context', 'log', 'route', 'service', 'response'
	);

	protected $contexts = array('root');

	protected $services = array('rest', 'info');

	protected $entities = array('log');

	public $namespace = 'Saltwater\Root';
}
