<?php

namespace Saltwater\Test;

use Saltwater\Thing\Module;

class Test extends Module
{
	/**
	 * In a modules $require, you set up the modules that need to be loaded
	 * in saltwater in order to have this module function properly.
	 */
	protected $require = array(
		'module' => array(
			/**
			 * We load the RedBean Module first (for a simple REST layer and
			 * other database stuff)
			 */
			'Saltwater\RedBean\RedBean',

			/**
			 * The App Module gives us a router and a way to output a response
			 */
			'Saltwater\App\App'
		)
	);

	protected $provide = array(
		/**
		 * The RedBean DbProvider requires a ConfigProvider to tell its the
		 * database details, so we make a dummy one for this test.
		 *
		 * Furthermore, we don't want an actual HTTP output, so we fake our
		 * router and the response a bit to make testing easier
		 */
		'provider' => array(
			'config', 'response', 'route'
		)
	);
}
