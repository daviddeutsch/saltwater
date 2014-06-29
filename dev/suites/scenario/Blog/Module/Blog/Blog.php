<?php

namespace Saltwater\Blog;

use Saltwater\Salt\Module;

class Blog extends Module
{
	/**
	 * In a modules $require, you set up the modules that need to be loaded
	 * in saltwater in order to have this module function properly.
	 *
	 * For this module to pass unit tests, we include the Test\Test Module,
	 * usually, you would include Saltwater\App\App and Saltwater\RedBean\RedBean
	 */
	protected $require = array(
		'module' => array('Saltwater\Test\Test')
	);

	protected $provide = array(
		/**
		 * This creates a new master context for this application
		 */
		'context' => array(
			'Blog'
		),
		/**
		 * Defining them as services tells the router that they can be accessed
		 * through /article[/:id] and /comment[/:id]
		 */
		'service' => array(
			'article', 'comment'
		),

		/**
		 * Finally, defining something as an entity is a signal for the RedBean
		 * module to track its lifecycle in an update stream
		 */
		'entity' => array(
			'comment'
		)
	);
}
