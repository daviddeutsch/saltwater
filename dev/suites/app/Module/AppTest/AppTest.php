<?php

namespace Saltwater\AppTest;

use Saltwater\Salt\Module;

class AppTest extends Module
{
	protected $require = array(
		'module' => array('Saltwater\App\App')
	);

	protected $provide = array(
		'context' => array('AppTest'),
		'service' => array('test')
	);
}
