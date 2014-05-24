<?php

namespace Saltwater\Blog;

use Saltwater\Thing\Module;

class Blog extends Module
{
	public static $name = 'blog';

	public static $namespace = 'Saltwater\Blog';

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
		 * We define 'article' and 'comment' as entities so that the RedBean
		 * Module can load them properly.
		 *
		 * Defining them as services tells the router that they can be accessed
		 * through /article[/:id] and /comment[/:id]
		 */
		'service' => array(
			'article', 'comment'
		),
		'entity' => array(
			'article', 'comment',
			'article-comment', 'comment-comment'
		)
	);
}
