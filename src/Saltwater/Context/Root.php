<?php

namespace Saltwater\Context;

use Saltwater\Server as S;

class Root extends Context
{
	public $root = true;

	public function getDB()
	{
		return S::$r;
	}
}
