<?php

namespace Saltwater\Blog;

use Saltwater\Thing\Module;

class Blog extends Module
{
	/**
	 * In a modules $require, you set up the modules that need to be loaded
	 * in saltwater in order to have this module function properly.
	 *
	 * For this module, we load the RedBean Module first (for a simple REST
	 * layer and other database stuff) and the App Module on top of that
	 * (which g
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
		),

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
		)/*,
		/**
		 * We define 'article' and 'comment' as entities so that the RedBean
		 * Module can load them properly.
		 */
		/*'entity' => array(
			'article', 'comment',
			'article-comment', 'comment-comment'
		)*/
	);
}
