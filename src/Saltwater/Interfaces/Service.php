<?php

namespace Saltwater\Interfaces;

use \Saltwater\Thing\Context;
use \Saltwater\Thing\Module;

/**
 * Service Interface
 *
 * A Service encapsulates functionality
 */
interface Service
{
	/**
	 * @param Context|null $context
	 * @param Module|null  $module
	 *
	 * @return void
	 */
	public function __construct( $context=null, $module=null );

	/**
	 * @param $context
	 *
	 * @return void
	 */
	public function setContext( $context );

	public function setModule( $module );

	/**
	 * Check whether a method is callable in this service
	 *
	 * @param string $method
	 *
	 * @return bool
	 */
	public function isCallable( $method );
}
