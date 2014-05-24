<?php

namespace Saltwater\Blog\Provider;

use Saltwater\App\Provider\Route as AppRoute;

class Route extends AppRoute
{
	protected function getInput()
	{
		return $GLOBALS['input'];
	}
}
