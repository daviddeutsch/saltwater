<?php

namespace Saltwater\RedBeanTest\Service;

use Saltwater\Server as S;
use Saltwater\RedBean\Service\Rest;

class Test extends Rest
{
	public function getCustom()
	{
		return 'itWorked';
	}
}
