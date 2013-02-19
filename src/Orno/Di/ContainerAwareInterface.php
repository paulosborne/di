<?php namespace Orno\Di;

interface ContainerAwareInterface
{
    /**
     * Get an instance of the container
     *
     * @return Orno\Di\Container
     */
    public function getContainer();
}
