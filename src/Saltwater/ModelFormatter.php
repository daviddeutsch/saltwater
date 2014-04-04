<?php

namespace Saltwater;

use RedBean_IModelFormatter;

use Saltwater\Server as S;

class ModelFormatter implements RedBean_IModelFormatter
{
	public function formatModel( $name )
	{
		return S::formatModel($name);
	}
}
