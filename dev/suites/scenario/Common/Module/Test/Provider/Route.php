<?php

namespace Saltwater\Test\Provider;

use Saltwater\Server as S;
use Saltwater\App\Provider\Route as AppRoute;

class Route extends AppRoute
{
	public function go()
	{
		S::$n->response('test')->response(
			$this->resolveChain( json_decode($GLOBALS['mock_input']) )
		);
	}

	protected function getURI()
	{
		return $GLOBALS['PATH'];
	}

	protected function getHTTP()
	{
		return $GLOBALS['METHOD'];
	}
}
