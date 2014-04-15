<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Common\Factory;

class Context implements Factory
{
	public static function get( $name, $context=null )
	{
		if ( empty($context->namespace) ) {
			$namespace = str_replace('\Provider', '', __NAMESPACE__);
		} else {
			$namespace = $context->namespace;
		}

		$class = $namespace . '\Context\\' . ucfirst($name);

		return new $class($context);
	}
}
