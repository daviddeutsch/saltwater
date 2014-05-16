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

		if ( class_exists($class) ) return new $class($context);

		if ( in_array($name, $context->services) ) {
			return S::$n->service->get('rest', $context);
		} elseif ( !empty($context->parent) ) {
			return S::$n->service->get(
				U::namespacedClassToDashed($class),
				$context->parent
			);
		}

		return null;
	}

	/**
	 * @param \Saltwater\Thing\Context $context
	 * @param string $service
	 */
	private function getServiceClass( $context, $service )
	{
		$class = U::className($context->namespace, 'service', $service);

		if ( class_exists($class) ) return $class;

		$class = U::className('saltwater', 'root', 'service', $service);

		if ( class_exists($class) ) return $class;

		$class = U::className($context->namespace, 'service', 'rest');

		if ( class_exists($class) ) return $class;

		return U::className('saltwater', 'root', 'service', 'rest');
	}
}
