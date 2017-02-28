<?php

namespace Saltwater\Overload;

use Saltwater\Salt\Module;

/**
 * Class Overload
 *
 * @package Saltwater\Overload
 *
 * @require Saltwater\Test\Test
 * @provide {"provider"
 */
class Overload extends Module
{
    protected $require = array(
        'module' => array('Saltwater\Test\Test')
    );

    protected $provide = array(
        'provider' => array('config')
    );
}
