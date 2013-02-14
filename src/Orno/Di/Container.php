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
     * Should the container automatically resolve dependencies?
     *
     * @var boolean
     */
    protected $autoResolve = false;

    /**
     * Register a class name, closure or fully configured item with the container,
     * we will handle dependencies at the time it is requested
     *
     * @param  string  $alias
     * @param  mixed   $object
     * @param  boolean $shared
     * @return void
     */
    public function register($alias, $object = null, $shared = false)
    {
        // if $object is null we assume the $alias is a class name that
        // needs to be registered
        if (is_null($object)) {
            $object = $alias;
        }

        // do we want to store this object as a singleton?
        $this->values[$alias]['shared'] = $shared === true ?: false;

        // if the $object is a string and $autoResolve is turned off we get a new
        // Definition instance to allow further configuration of our object
        if (is_string($object) && $this->autoResolve === false) {
            $object = new Definition($object);
        }

        // simply store whatever $object is in the container and resolve it
        // when it is requested
        $this->values[$alias]['object'] = $object;

        // if the $object has been set as a Definition, return the instance of
        // definition for any further runtime configuration
        if ($object instanceof Definition) {
            return $object;
        }
    }

    /**
     * Resolve and return the requested item
     *
     * @param  string $alias
     * @return mixed
     */
    public function resolve($alias)
    {
        $object = null;

        if (! array_key_exists($alias, $this->values)) {
            $this->register($alias);
        }

        // if the item is currently stored as a shared item we just return it
        if (array_key_exists($alias, $this->shared)) {
            return $this->shared[$alias];
        }

        // if the item is a factory closure or a Definition instance let's just invoke it
        if ($this->values[$alias]['object'] instanceof Closure || $this->values[$alias]['object'] instanceof Definition) {
            $object = $this->values[$alias]['object']();
        }

        // if we've got this far and $autoResolve is turned on then we need to
        // build the object and resolve it's dependencies
        if ($this->autoResolve === true && is_null($object)) {
            $object = $this->build($alias, $this->values[$alias]['object']);
        }

        // do we need to save it as a shared item?
        if ($this->values[$alias]['shared'] === true) {
            $this->shared[$alias] = $object;
        }

        if (is_null($object)) {
            throw new \RuntimeException('Unable to resolve an instance of ' . $alias);
        }

        return $object;
    }

    /**
     * Takes the $object and instantiates it with all dependencies injected
     * into it's constructor
     *
     * @param  string $alias
     * @param  string $object
     * @return object
     */
    public function build($alias, $object)
    {
        $reflection = new ReflectionClass($object);
        $construct = $reflection->getConstructor();

        // if the $object has no constructor we just return the object
        if (is_null($construct)) {
            return new $object;
        }

        // get the constructors params to pass to dependencies method
        $params = $construct->getParameters();

        // resolve an array of dependencies
        $dependencies = $this->dependencies($object, $params);

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Recursively resolve dependencies, and dependencies of dependencies etc.. etc..
     * Will first check if the parameters type hint is instantiable and resolve that, if
     * not it will attempt to resolve an implementation from the param annotation
     *
     * @param  string $object
     * @param  array  $params
     * @return array
     */
    public function dependencies($object, $params)
    {
        $dependencies = [];

        foreach ($params as $param) {
            $dependency     = $param->getClass();
            $dependencyName = $dependency->getName();

            // if the type hint is instantiable we just resolve it
            if ($dependency->isInstantiable()) {
                $dependencies[] = $this->resolve($dependencyName);
                continue;
            }

            // has the dependency been registered to an alias with the container?
            // e.g. Interface to Implementation
            if (array_key_exists($dependencyName, $this->values)) {
                $dependencies[] = $this->resolve($dependencyName);
                continue;
            }

            // if we've got this far we can check the @param annotations from the
            // constructors DocComment to try and resolve a concrete implementation
            $matches = $this->getConstructorParams($object);

            // loop through constructor parameters and match any annotations to resolve
            if ($matches !== false) {
                foreach ($matches['name'] as $key => $val) {
                    if ($val === $param->getName()) {
                        $dependencies[] = $this->resolve($matches['type'][$key]);
                        break;
                    }
                }
            }
        }

        return $dependencies;
    }

    /**
     * Accepts the name of an object in string form and returns an
     * array of param matches from the constructor docComment
     *
     * @param  string $object
     * @return array|boolean
     */
    public function getConstructorParams($object)
    {
        $docComment = (new ReflectionMethod($object, '__construct'))->getDocComment();

        $result = preg_match_all(
            '/@param[\t\s]*(?P<type>[^\t\s]*)[\t\s]*\$(?P<name>[^\t\s]*)/sim',
            $docComment, $matches
        );

        return $result > 0 ? $matches : false;
    }

    /**
     * Sets the $autoResolve option
     *
     * @param  boolean   $auto
     * @return Container $this
     */
    public function autoResolve($auto)
    {
        $this->autoResolve = (bool) $auto;
        return $this;
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