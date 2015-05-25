<?php

namespace Saltwater\Capability;

use Saltwater\Salt\Module;

class Capability extends Module
{
	protected $require = array( 'module' => array('Saltwater\Root\Root') );
	protected $provide = array( 'provider' => array('capability') );
}
