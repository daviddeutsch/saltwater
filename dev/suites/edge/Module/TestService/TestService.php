<?php

namespace Saltwater\TestService;

use Saltwater\Thing\Module;

class TestService extends Module
{
	protected $require = array(
		'module' => array('Saltwater\Root\Root')
	);

	protected $provide = array(
		'context' => array('TestService'),
		'service' => array('lacking')
	);
}
