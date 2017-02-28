<?php

namespace Saltwater\Bus\Water;

class Chain
{
    private $chain;

    public function push($call)
    {
        $this->chain[] = $call;
    }
}
