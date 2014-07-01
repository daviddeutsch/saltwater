<?php

namespace Saltwater\Overload;

use Saltwater\Salt\Module;

class Overload extends Module
{
	protected $require = array(
		'module' => array('Saltwater\Test\Test')
	);

	protected $provide = array(
		'provider' => array('config')
	);
}
