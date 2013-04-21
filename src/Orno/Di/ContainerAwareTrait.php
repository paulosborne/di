<?php

/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Di;

/**
 * Container Aware Trait
 *
 * A trait to allow setting and getting of the container instance
 */
trait ContainerAwareTrait
{
    /**
     * The instance of the container
     *
     * @var Container\Di\Container
     */
    public $container = null;

    /**
     * Get Container
     *
     * Call the static::getContainer method to return an instance of the container
     *
     * @return Container\Di\Container
     */
    public function getContainer()
    {
        if (is_null($this->container)) {
            $this->container = Container::getContainer();
        }

        return $this->container;
    }

    /**
     * Set Container
     *
     * Inject an instance of the ContainerInterface to override the container being used
     *
     * @param  Container\Di\ContainerInterface
     * @return void
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
