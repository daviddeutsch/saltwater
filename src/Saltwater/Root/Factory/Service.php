<?php

namespace Saltwater\Root\Factory;

use Saltwater\Server as S;
use Saltwater\Thing\Factory;
use Saltwater\Utils as U;

class Service extends Factory
{
	/**
	 * @param string                   $name
	 * @param \Saltwater\Thing\Context $context
	 *
	 * @return \Saltwater\Thing\Provider|string
	 */
	public static function getFactory( $name, $context=null )
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
			return S::$n->service('rest', $context);
		} elseif ( !empty($context->parent) ) {
			return S::$n->service(
				U::namespacedClassToDashed($class),
				$context->parent
			);
		}

		return null;
	}
}
