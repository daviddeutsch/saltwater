<?php

namespace Saltwater\Capability\Provider;

use Saltwater\Utils as U;
use Saltwater\Salt\Provider;
use Saltwater\Salt\Context as SwContext;
use Saltwater\Salt\Service as SwService;

class Service extends Provider
{
	/**
	 * @param string  $name
	 * @param SwContext $context
	 *
	 * @return SwService
	 */
	public function get( $name, $context )
	{
		$class = $this->getServiceClass($context, $name);

		// TODO: RB Fallback is rather dirty, try parent context or fail out
		return new $class($context);
	}

	/**
	 * @param SwContext $context
	 * @param string    $service
	 */
	private function getServiceClass( $context, $service )
	{
		// See whether the current context namespace has this service
		$class = U::className($context->namespace, 'service', $service);

		if ( class_exists($class) ) return $class;

		return $this->getServiceClassFallback($context, $service);
	}

	private function getServiceClassFallback( $context, $service )
	{
		// Check whether we have a RestService in the context namespace
		$class = U::className($context->namespace, 'service', 'rest');

		if ( class_exists($class) ) return $class;

		// Next up, try for a root service
		$class = U::className('saltwater', 'root', 'service', $service);

		if ( class_exists($class) ) return $class;

		// Fall back to the RedBean RestService
		// TODO: Needs to be decoupled
		return 'Saltwater\RedBean\Service\Rest';
	}
}
