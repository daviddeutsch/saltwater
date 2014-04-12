<?php
namespace Saltwater\Root\Service;

use Saltwater\Service as Service;

class Info extends Service
{
	public function getInfo()
	{
		return $this->context->getInfo();
	}
}
