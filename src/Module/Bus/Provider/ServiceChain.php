<?php

namespace Saltwater\App\Provider;

use Saltwater\Bus\Water\Chain;
use Saltwater\Salt\Provider;
use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Salt\Service;
use Saltwater\Salt\Context;

class ServiceChain extends Provider
{
    private $chain;

    public function __construct()
    {
        $this->chain = new Chain();
    }

    public function resolve($input, $result = null)
    {
        $length = count($this->chain);

        $service = new Service;

        for ($i = 0; $i < $length; ++$i) {
            $result = $this->chain(
                $service, $this->chain[$i], $input, $result, ($i == $length - 1)
            );
        }

        return $result;
    }

    /**
     * @param Service $service
     * @param object  $item
     * @param mixed   $input
     * @param mixed   $result
     * @param bool    $last
     *
     * @return mixed|null
     */
    private function chain(&$service, &$item, $input, $result, $last)
    {
        $item->context->pushData($result);

        $service->setContext($item->context);

        if (!$service->prepareCall($item)) {
            $service = S::$n->service->get($item->service, $item->context);
        }

        return $service->call($item, $last ? $input : null);
    }

    /**
     * @param Context $context
     * @param string  $cmd
     * @param array   $path
     */
    protected function explode($context, $cmd, $path)
    {
        $root = array_shift($path);

        // This is for simple commands upon an established service
        if (empty($path) && !empty($this->chain)) {
            $this->push($context, $cmd, $root);

            return;
        }

        if ($c = S::$n->context->get($root, $context)) {
            $context = $c;

            $root = array_shift($path);
        }

        $this->explodePush($path, $context, $cmd, $root);
    }

    /**
     * @param Context $context
     * @param string  $cmd
     */
    private function explodePush($path, $context, $cmd, $root)
    {
        $next = array_shift($path);

        // Either push a call on the last service or a new one into the chain
        if (empty($path)) {
            $this->push($context, $cmd, $root, $next);
        } else {
            $this->push($context, 'get', $root, $next);

            // We have leftovers!
            $this->explode($context, $cmd, $path);
        }
    }

    /**
     * @param Context $context
     * @param string  $cmd
     * @param string  $service
     * @param string  $path
     */
    protected function push($context, $cmd, $service, $path = null)
    {
        $method = $service;

        if (!empty($path) && !is_numeric($path)) {
            $method = $path;

            $path = null;
        }

        $this->chain[] = (object) array(
            'context'  => $context,
            'http'     => $cmd,
            'service'  => $service,
            'method'   => $method,
            'function' => $cmd . U::dashedToCamelCase($method),
            'path'     => $path
        );
    }

}
