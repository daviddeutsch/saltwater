<?php

namespace Saltwater\Water;

class Registry extends \ArrayObject
{
    /**
     * Return true if the input is a registered Salt
     *
     * @param string $name in the form "type.name"
     *
     * @return bool
     */
    public function exists($name)
    {
        return in_array($name, (array) $this);
    }

    /**
     * Return the bitmask integer of a Salt
     *
     * @param string $name in the form "type.name"
     *
     * @return bool|int
     */
    public function bit($name)
    {
        return array_search($name, (array) $this);
    }

    /**
     * Register a Salt and return its bitmask integer
     *
     * @param $name
     *
     * @return number
     */
    public function append($name)
    {
        if ($id = $this->bit($name)) {
            return $id;
        }

        $id = pow(2, count($this));

        $this[$id] = $name;

        return $id;
    }
}
