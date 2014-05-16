<?php

namespace Saltwater\App;

use Saltwater\Thing\Module;

class App extends Module
{
	public static $name = 'app';

	public static $namespace = 'Saltwater\App';

	protected $provide = array( 'provider' => array('route', 'response') );
}
