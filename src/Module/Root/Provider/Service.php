<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Provider;

class Service extends Provider
{
	public static function getProvider() { return new Service; }

	/**
	 * @param string                   $name
	 * @param \Saltwater\Thing\Context $context
	 *
	 * @return \Saltwater\Thing\Service
	 */
	public function get( $name, $context )
	{
		$class = $this->getServiceClass($context, $name);

		// TODO: RB Fallback is rather dirty, try parent context or fail out
		return new $class($context);
	}

	/**
	 * @param \Saltwater\Thing\Context $context
	 * @param string $service
	 */
	private function getServiceClass( $context, $service )
	{
		// See whether the current context namespace has this service
		$class = U::className($context->namespace, 'service', $service);

		if ( class_exists($class) ) return $class;

		// Next up, try for a root service
		$class = U::className('saltwater', 'root', 'service', $service);

		if ( class_exists($class) ) return $class;

		// Check whether we have a RestService in the context namespace
		$class = U::className($context->namespace, 'service', 'rest');

		if ( class_exists($class) ) return $class;

		// Fall back to the RedBean RestService
		return 'Saltwater\RedBean\Service\Rest';
	}
}
