<?php

namespace Saltwater;

use Saltwater\Utils as U;

class Module
{
	protected $contexts = array();

	public function findContext()
	{
		foreach ( $this->contexts as $context ) {
			$class = 'Saltwater\\' . U::dashedToCamelCase($context);

			if ( !class_exists($class) ) return $class;
		}

		return false;
	}
}
