<?php

namespace Saltwater\Capability;

use Saltwater\Salt\Module;

/**
 * Class Capability
 *
 * @package Saltwater\Capability
 * @Require {'module': 'Saltwater\Root\Root'}
 * @Provide {'provider': 'capability'}
 */
class Capability extends Module
{
	protected $require = array( 'module' => array('Saltwater\Root\Root') );
	protected $provide = array( 'provider' => array('capability') );
}
