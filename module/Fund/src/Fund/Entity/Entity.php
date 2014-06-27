<?php

namespace Fund\Entity;

/**
* Abstract Entity (Layer supertype)
*/
abstract class Entity
{
    /**
    * Constructor
    *
    * @param mixed $options inital options
    */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
    * Overloading: set object property
    *
    * @param string $name
    * @return mixed
    */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException('Invalid user property');
        }
        $this->$method($value);
    }

    /**
    * Overloading: retrieve object property
    *
    * @param  string $name
    * @return mixed
    */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException('Invalid user property');
        }

        return $this->$method();
    }

    /**
    * Set properties using an array
    *
    * @param array $options the options
    */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }

        return $this;
    }
}
