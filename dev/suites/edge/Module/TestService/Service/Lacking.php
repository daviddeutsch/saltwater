<?php
namespace Saltwater\TestService\Service;

use Saltwater\Server as S;
use Saltwater\Salt\Service;

class Lacking extends Service
{
	public function getTrue()
	{
		return true;
	}
}
