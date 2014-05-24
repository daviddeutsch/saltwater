<?php

namespace Saltwater\Root;

use Saltwater\Thing\Module;

class Root extends Module
{
	protected $provide = array(
		'provider' => array('context', 'service'),
		'context' => array('root'),
		'service' => array('info')
	);
}
