<?php namespace Orno\Di;

use Closure, ArrayAccess, ReflectionMethod, ReflectionClass;

class Container implements ArrayAccess
{
    /**
     * Items registered with the container
     *
     * @var array
     */
    protected $values = [];

    /**
     * Shared instances
     *
     * @var array
     */
    protected $shared = [];

    /**
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Register a class name, closure or fully configured item with the container,
     * we will handle dependencies at the time it is requested
     *
     * @param  string  $alias
     * @param  mixed   $concrete
     * @param  boolean $shared
     * @return void
     */
    public function register($alias, $concrete = null, $shared = false)
    {
        // if $concrete is null we assume the $alias is a class name that
        // needs to be registered
        if (is_null($concrete)) {
            $concrete = $alias;
        }

        // simply store whatever $concrete is in the container and resolve it
        // when it is requested
        $this->values[$alias]['concrete'] = $concrete;
        $this->values[$alias]['shared'] = $shared === true ?: false;
    }

    /**
     * Resolve and return the requested item
     *
     * @param  string $alias
     * @return mixed
     */
    public function resolve($alias)
    {
        if (! array_key_exists($alias, $this->values)) {
            $this->register($alias);
        }

        // if the item is currently stored as a shared item we just return it
        if (array_key_exists($alias, $this->shared)) {
            return $this->shared[$alias];
        }

        // if the item is a closure or pre-configured object we just return it
        if ($this->values[$alias]['concrete'] instanceof Closure) {
            // TODO: sharing for closures!!
            return $this->values[$alias]['concrete']();
        }

        // if we've got this far we need to build the object and resolve it's dependencies
        $object = $this->build($alias, $this->values[$alias]['concrete']);

        // do we need to save it as a shared item?
        if ($this->values[$alias]['shared'] === true) {
            $this->shared[$alias] = $object;
        }

        return $object;
    }

    /**
     * Takes the $concrete and instantiates it with all dependencies injected
     * into it's constructor
     *
     * @param  string $alias
     * @param  string $concrete
     * @return object
     */
    public function build($alias, $concrete)
    {
        $reflection = new ReflectionClass($concrete);
        $construct = $reflection->getConstructor();

        // if the $concrete has no constructor we just return the object
        if (is_null($construct)) {
            return new $concrete;
        }

        // get the constructors params to pass to dependencies method
        $params = $construct->getParameters();

        // resolve an array of dependencies
        $dependencies = $this->dependencies($concrete, $params);

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Recursively resolve dependencies, and dependencies of dependencies etc.. etc..
     * Will first check if the parameters type hint is instantiable and resolve that, if
     * not it will attempt to resolve an implementation from the param annotation
     *
     * @param  string $concrete
     * @param  array  $params
     * @return array
     */
    public function dependencies($concrete, $params)
    {
        $dependencies = [];

        foreach ($params as $param) {
            $dependency = $param->getClass();

            // if the type hint is instantiable we just resolve it
            if ($dependency->isInstantiable()) {
                $dependencies[] = $this->resolve($dependency->getName());
                continue;
            }

            // if the type hint is not instantiable, it could be an interface so we have a last
            // ditch attempt to resolve a class from the @param annotation
            $matches = $this->parseDocComment($concrete);

            if ($matches !== false) {
                foreach ($matches['name'] as $key => $val) {
                    if ($val === $param->getName()) {
                        $dependencies[] = $this->resolve($matches['type'][$key]);
                        break;
                    }
                }
            }
        }

        return (array) $dependencies;
    }

    /**
     * Accepts the name of an object in string form and returns the an
     * array of param matches from the constructor docComment
     *
     * @param  string $concrete
     * @return array|boolean
     */
    public function parseDocComment($concrete)
    {
        $docComment = (new ReflectionMethod($concrete, '__construct'))->getDocComment();

        $results = preg_match_all(
            '/@param[\t\s]*(?P<type>[^\t\s]*)[\t\s]*\$(?P<name>[^\t\s]*)/sim',
            $docComment, $matches
        );

        return $results > 0 ? $matches : false;
    }

    /**
     * Gets a value from the container
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->resolve($key);
    }

    /**
     * Registers a value with the container
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->register($key, $value);
    }

    /**
     * Destroys an item in the container
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->values[$key]);
    }

    /**
     * Checks if an item is set
     *
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->values[$key]);
    }
}
