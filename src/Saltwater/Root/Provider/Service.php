<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;

class Service
{
	public function get( $name, $context )
	{
		$class = $context->namespace . '\Service\\' . ucfirst($name);

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
