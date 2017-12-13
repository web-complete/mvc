<?php

namespace WebComplete\mvc\router;

class Routes implements \ArrayAccess, \Iterator
{

    /**
     * @var array
     */
    private $config;
    private $position = 0;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->config;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->config[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->config[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    /**
     * Return the current element
     * @return array
     */
    public function current()
    {
        return $this->config[$this->position];
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     * @return bool
     */
    public function valid()
    {
        return isset($this->config[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @param array $routeDefinition
     * @param string|null $beforeRoute
     */
    public function addRoute(array $routeDefinition, string $beforeRoute = null)
    {
        if ($beforeRoute) {
            /**
             * @var int $k
             * @var array $route
             */
            foreach ((array)$this->config as $k => $route) {
                if ($route[1] === $beforeRoute) {
                    $k > 1
                        ? \array_splice($this->config, $k, 0, [$routeDefinition])
                        : \array_unshift($this->config, $routeDefinition);
                }
            }
        } else {
            \array_unshift($this->config, $routeDefinition);
        }
    }
}
