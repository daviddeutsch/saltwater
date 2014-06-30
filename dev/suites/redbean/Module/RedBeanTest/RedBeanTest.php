<?php

namespace Saltwater\RedBeanTest;

use Saltwater\Salt\Module;

class RedBeanTest extends Module
{
	protected $require = array(
		'module' => array('Saltwater\RedBean\RedBean')
	);

	protected $provide = array(
		'context' => array('RedBeanTest'),
		'service' => array('test')
	);
}
