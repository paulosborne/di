<?php namespace Orno\Di;

use ReflectionClass;
use ReflectionMethod;

class Definition implements ContainerAwareInterface
{
    /**
     * Instance of the container object
     *
     * @var Orno\Di\Container
     */
    protected $container;

    /**
     * The fully qualified namespace of the instance to return
     *
     * @var string
     */
    protected $class;

    /**
     * Array of constructor arguments to be injected
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Associative array of methods to call before returning the object
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Constructor
     *
     * @param string $class
     */
    public function __construct($class = null)
    {
        $this->class = $class;
        $this->container = $this->getContainer();
    }

    /**
     * Get an instance of the container
     *
     * @return Orno\Di\Container
     */
    public function getContainer()
    {
        return Container::getContainer();
    }

    /**
     * Configures and returns the class associated with this instance
     *
     * @return object
     */
    public function __invoke()
    {
        $object = null;

        if (! $this->hasClass()) {
            throw new \RuntimeException('The definition has no class associated with it');
        }

        if ($this->hasArguments()) {
            $reflectionClass = new ReflectionClass($this->class);

            $arguments = [];

            foreach ($this->arguments as $arg) {
                if (is_string($arg) && $this->container->registered($arg)) {
                    $arguments[] = $this->container->resolve($arg);
                    continue;
                }
                $arguments[] = $arg;
            }

            $object = $reflectionClass->newInstanceArgs($arguments);
        } else {
            $object = new $this->class;
        }

        if ($this->hasMethodCalls()) {
            foreach ($this->methods as $method => $args) {
                $reflectionMethod = new ReflectionMethod($object, $method);

                $methodArgs = [];

                foreach ((array) $args as $arg) {
                    if (is_string($arg) && $this->container->registered($arg)) {
                        $methodArgs[] = $this->container->resolve($arg);
                        continue;
                    }
                    $methodArgs[] = $arg;
                }

                $reflectionMethod->invokeArgs($object, $methodArgs);
            }
        }

        return $object;
    }

    /**
     * Sets the class for this instance
     *
     * @param  string     $class
     * @return Definition $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Checks if this Definition has a class associated with it
     *
     * @return boolean
     */
    public function hasClass()
    {
        return (! is_null($this->class));
    }

    /**
     * Sets a constructor argument for this instance
     *
     * @param  mixed      $argument
     * @return Definition $this
     */
    public function withArgument($argument)
    {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * Proxy to withArgument() method, accepts array of arguments
     *
     * @param  array      $arguments
     * @return Definition $this
     */
    public function withArguments(array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->withArgument($argument);
        }

        return $this;
    }

    /**
     * Checks if this Definition has arguments to inject
     *
     * @return boolean
     */
    public function hasArguments()
    {
        return (! empty($this->arguments));
    }

    /**
     * Adds a method call to this instance
     *
     * @param  string     $method
     * @param  array      $arguments
     * @return Definition $this
     */
    public function withMethodCall($method, array $arguments = [])
    {
        $this->methods[$method] = $arguments;

        return $this;
    }

    /**
     * Proxy to withMethodCall() method, accepts array of method calls
     *
     * @param  array      $methods
     * @return Definition $this
     */
    public function withMethodCalls(array $methods = [])
    {
        foreach ($methods as $method => $arguments) {
            $this->withMethodCall($method, $arguments);
        }

        return $this;
    }

    /**
     * Checks if this definition has methods to call
     *
     * @return boolean
     */
    public function hasMethodCalls()
    {
        return (! empty($this->methods));
    }
}
