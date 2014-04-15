<?php

namespace Saltwater\Common;

interface Factory
{
	public static function get( $name, $input=null );
}
