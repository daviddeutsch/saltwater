<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;

class Service
{
	public function get( $name, $context )
	{
		$class = $context->namespace . '\Service\\' . ucfirst($name);

		if ( class_exists($class) ) return $class;

		$root = 'Saltwater\Root';

		if ( in_array($name, $context->services) ) {
			return $root . '\Service\Rest';
		} elseif ( !empty($context->parent) ) {
			return S::service($context->parent, $name);
		} else {
			return '';
		}
	}
}
