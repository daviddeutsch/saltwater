<?php
namespace MangroveServer\Service;

class Info extends AbstractService
{
	public function getInfo()
	{
		return $this->context->getInfo();
	}

}
