<?php

/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Di;

use ReflectionClass;
use ReflectionMethod;
use Orno\Di\ContainerInterface;

/**
 * Definition
 *
 * A definition of an item registered with the dependency injection container. Holds
 * information regarding constructor injection and any method calls to be invoked
 * before returning an instance of the item
 */
class Definition
{
    use ContainerAwareTrait;

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
     * Array of methods to call before returning the object
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Does the object need to be auto resolved?
     *
     * @var boolean
     */
    protected $auto;

    /**
     * Constructor
     *
     * @param string                      $class
     * @param \Orno\Di\ContainerInterface $container
     * @param boolean                     $auto
     */
    public function __construct($class = null, ContainerInterface $container, $auto = false)
    {
        $this->class = $class;
        $this->container = $container;
        $this->auto = $auto;
    }

    /**
     * Magic Invoke
     *
     * Configure and returns the object associated with this definition
     *
     * @return object
     */
    public function __invoke()
    {
        $object = null;

        if (! $this->hasClass()) {
            throw new \RuntimeException(
                'The definition has no class associated with it'
            );
        }

        $object = $this->handleConstructorInjection();

        return $this->handleMethodCalls($object);
    }

    /**
     * Handle Constructor Injection
     *
     * Instantiates the object with any constructor arguments injected
     *
     * @return object
     */
    public function handleConstructorInjection()
    {
        if ($this->hasArguments()) {
            $reflectionClass = new ReflectionClass($this->class);
            $arguments = [];

            foreach ($this->arguments as $arg) {
                if (is_string($arg) && (class_exists($arg) || $this->container->registered($arg))) {
                    $arguments[] = $this->container->resolve($arg);
                    continue;
                }
                $arguments[] = $arg;
            }

            $object = $reflectionClass->newInstanceArgs($arguments);
        } else {
            if ($this->auto === false) {
                $object = new $this->class;
            } else {
                $object = $this->container->build($this->class);
            }
        }

        return $object;
    }

    /**
     * Handle Method Calls
     *
     * Invokes all methods that are associated with the definition
     *
     * @param  object $object
     * @return object
     */
    public function handleMethodCalls($object)
    {
        if ($this->hasMethodCalls()) {
            foreach ($this->methods as $method) {
                $reflectionMethod = new ReflectionMethod($object, $method['method']);

                $methodArgs = [];

                foreach ((array) $method['arguments'] as $arg => $params) {
                    if (is_string($arg) && $this->container->registered($arg)) {
                        $methodArgs[] = $this->container->resolve($arg, (array) $params);
                        continue;
                    }

                    if (is_integer($arg) && is_string($params) && $this->container->registered($params)) {
                        $methodArgs[] = $this->container->resolve($params);
                        continue;
                    }

                    $methodArgs[] = $params;
                }

                $reflectionMethod->invokeArgs($object, $methodArgs);
            }
        }

        return $object;
    }

    /**
     * Has Class?
     *
     * Checks if the definition has a class associated with it
     *
     * @return boolean
     */
    public function hasClass()
    {
        return (! is_null($this->class));
    }

    /**
     * With Argument
     *
     * Sets a constructor argument for the definition
     *
     * @param  mixed $argument
     * @return \Orno\Di\Definition
     */
    public function withArgument($argument)
    {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * With Arguments
     *
     * Proxy to withArgument method, accepts an array of arguments
     *
     * @param  array $arguments
     * @return \Orno\Di\Definition
     */
    public function withArguments(array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->withArgument($argument);
        }

        return $this;
    }

    /**
     * Has Arguments?
     *
     * Checks if the definition has registered constructor arguments to inject
     *
     * @return boolean
     */
    public function hasArguments()
    {
        return (! empty($this->arguments));
    }

    /**
     * With Method Call
     *
     * Sets a method call for the definition
     *
     * @param  string $method
     * @param  array  $arguments
     * @return \Orno\Di\Definition
     */
    public function withMethodCall($method, array $arguments = [])
    {
        $this->methods[] = [
            'method' => $method,
            'arguments' =>$arguments
        ];

        return $this;
    }

    /**
     * With Method Calls
     *
     * Proxy to withMethodCall method, accepts array of method calls with method arguments
     *
     * @param  array $methods
     * @return \Orno\Di\Definition
     */
    public function withMethodCalls(array $methods = [])
    {
        foreach ($methods as $method => $arguments) {
            $this->withMethodCall($method, $arguments);
        }

        return $this;
    }

    /**
     * Has Method Calls?
     *
     * Checks if this definition has any registered methods to invoke
     *
     * @return boolean
     */
    public function hasMethodCalls()
    {
        return (! empty($this->methods));
    }
}
