<?php

namespace Saltwater\TestService\Service;

use Saltwater\Server as S;
use Saltwater\Salt\Service;

class Extensive extends Service
{
    public function getProviders($data, $path, $context)
    {
        return array($data, $path, $context);
    }
}
