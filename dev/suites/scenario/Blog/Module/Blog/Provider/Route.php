<?php

namespace Saltwater\Blog\Provider;

use Saltwater\Server as S;
use Saltwater\App\Provider\Route as AppRoute;

class Route extends AppRoute
{
	public function go()
	{
		S::$n->response('blog')->response(
			$this->resolveChain( json_decode($GLOBALS['mock_input']) )
		);
	}
}
