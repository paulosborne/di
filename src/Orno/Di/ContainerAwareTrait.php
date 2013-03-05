<?php namespace Orno\Di;

trait ContainerAwareTrait
{
    /**
     * @var Container\Di\Container
     */
    public $container = null;

    /**
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
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
