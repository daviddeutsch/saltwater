<?php

namespace Saltwater\Bus;

use Saltwater\Salt\Module;

class Bus extends Module
{
	protected $require = array( 'module' => array('Saltwater\Root\Root') );
	protected $provide = array( 'provider' => array('route', 'response') );
}
