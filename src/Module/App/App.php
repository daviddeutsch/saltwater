<?php

namespace Saltwater\App;

use Saltwater\Salt\Module;

class App extends Module
{
	protected $require = array( 'module' => array('Saltwater\Root\Root') );
	protected $provide = array( 'provider' => array('route', 'response') );
}
