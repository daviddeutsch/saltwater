<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Thing\Factory;
use Saltwater\Utils as U;

class Service extends Factory
{
	public static function get( $name, $context=null )
	{
		$class = $context->namespace
			. '\Service\\'
			. U::dashedToCamelCase($name);

		if ( class_exists($class) ) return $class;

		if ( in_array($name, $context->services) ) {
			return S::$n->service('rest', $context);
		} elseif ( !empty($context->parent) ) {
			return S::$n->service($name, $context->parent);
		} else {
			return '';
		}
	}
}
