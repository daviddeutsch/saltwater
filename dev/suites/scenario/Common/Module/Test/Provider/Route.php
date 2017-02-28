<?php

namespace Saltwater\Test\Provider;

use Saltwater\Server as S;
use Saltwater\App\Provider\Route as AppRoute;

class Route extends AppRoute
{
    /**
     * @param Response     $response
     * @param ServiceChain $serviceChain
     */
    public function go($response, $serviceChain)
    {
        return $response->response(
            $serviceChain->resolveChain(json_decode($GLOBALS['mock_input']))
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
