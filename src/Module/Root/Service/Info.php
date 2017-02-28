<?php
namespace Saltwater\Root\Service;

use Saltwater\Salt\Service;

class Info extends Service
{
    public function getInfo()
    {
        return $this->context->getInfo();
    }
}
