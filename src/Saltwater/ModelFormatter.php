<?php

namespace Saltwater;

use RedBean_IModelFormatter;

class ModelFormatter implements RedBean_IModelFormatter
{
	public function formatModel( $model )
	{
		return 'MangroveServer\Models\\'
		. str_replace( ' ', '', ucwords(str_replace('_', ' ', $model)) );
	}
}
