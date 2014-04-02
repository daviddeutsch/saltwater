<?php
namespace Saltwater\Service;

class Info extends Service
{
	public function getInfo()
	{
		return $this->context->getInfo();
	}

}
