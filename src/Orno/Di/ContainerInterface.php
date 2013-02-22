<?php namespace Orno\Di;

interface ContainerInterface
{
    /**
     * Register an item with the container
     *
     * @param  string|closure|object $alias
     * @param  string|closure|object $object
     * @param  boolean               $shared
     * @return Orno\Di\Definition
     */
    public function register($alias, $object = null, $shared = false);

    /**
     * Resolve an item from the container
     *
     * @param  string $alias
     * @return object
     */
    public function resolve($alias);
}
