<?php

namespace Saltwater\Salt;

use Saltwater\Server as S;

/**
 * Services encapsulate application logic
 *
 * @package Saltwater\Salt
 */
class Service
{
    /** @var Context */
    protected $context = null;

    /** @var string */
    protected $module = null;

    /**
     * @param Context $context
     * @param string  $module
     *
     * @return void
     */
    public function __construct($context = null, $module = null)
    {
        $this->setContext($context);

        if (is_null($module) && !empty($context->module)) {
            $module = $context->module->getName();
        }

        $this->setModule($module);
    }

    /**
     * @param Context $context
     *
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @param string $module
     *
     * @return void
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * Ensure that a method can be called within this service
     *
     * @param string $method
     *
     * @return bool
     */
    public function isCallable($method)
    {
        return method_exists($this, $method);
    }

    /**
     * Prepare the calling of a method
     *
     * @param object $call
     *
     * @return bool
     */
    public function prepareCall($call)
    {
        return $this->isCallable($call->function);
    }

    /**
     * Attempt to execute a call on this service
     *
     * @param object $call
     * @param mixed  $data
     *
     * @return mixed
     */
    public function call($call, $data = null)
    {
        if (!$this->isCallable($call->function)) {
            return null;
        }

        return $this->executeCall($call, $call->function, $data);
    }

    /**
     * Execute a call
     *
     * @param object $call
     * @param string $method
     * @param mixed  $data
     *
     * @return mixed
     */
    protected function executeCall($call, $method, $data)
    {
        $reflect = new \ReflectionMethod($this, $method);

        // Check whether we need to inject parameters
        if ($reflect->getNumberOfParameters()) {
            return call_user_func_array(
                array($this, $method),
                $this->getMethodArgs($reflect, $call->path, $data)
            );
        }

        // No parameter assembly necessary
        return call_user_func(array($this, $method));
    }

    /**
     * Assemble injected method parameters
     *
     * Note: $path and $data are reserved parameters
     *
     * @param \ReflectionMethod $reflect
     * @param string            $path
     * @param mixed             $data
     *
     * @return array
     */
    private function getMethodArgs($reflect, $path, $data)
    {
        $args = array();
        foreach ($reflect->getParameters() as $parameter) {
            $name = $parameter->getName();

            if ($name == 'path') {
                $args[] = $path;
                continue;
            }

            if ($name == 'data') {
                $args[] = $data;
                continue;
            }

            $args[] = S::$n->provider($name, $this->module);
        }

        return $args;
    }
}
