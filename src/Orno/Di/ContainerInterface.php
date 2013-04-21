<?php

/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Di;

/**
 * Container Interface
 *
 * A contract to provide an implementation of a container
 */
interface ContainerInterface
{
    /**
     * Register
     *
     * Register an item with the container
     *
     * @param  mixed   $alias
     * @param  mixed   $object
     * @param  boolean $shared
     * @return \Orno\Di\Definition
     */
    public function register($alias, $object = null, $shared = false);

    /**
     * Resolve
     *
     * Resolve an item from the container
     *
     * @param  string $alias
     * @return object
     */
    public function resolve($alias);
}
