<?php

namespace Saltwater\Root;

use Saltwater\Thing\Module;

class Root extends Module
{
	protected $providers = array(
		'Db', 'Entity', 'Log', 'Route', 'Service', 'Response'
	);

	protected $contexts = array('Root');

	protected $services = array('Rest', 'Info');

	protected $entities = array('Log');
}
