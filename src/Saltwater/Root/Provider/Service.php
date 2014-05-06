<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Provider;

class Service extends Provider
{
	public static function getProvider() { return new Service(); }

	/**
	 * @param string                   $name
	 * @param \Saltwater\Thing\Context $context
	 *
	 * @return \Saltwater\Thing\Service
	 */
	public function get( $name, $context=null )
	{
		// TODO: This is still pretty dirty
		if ( strpos($name, '\\') ) {
			$class = $name;
		} else {
			$class = $context->namespace
				. '\Service\\'
				. U::dashedToCamelCase($name);
		}

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
}
