# Orno\Di - PHP Dependency Injection Container

[![Buildi Status](https://travis-ci.org/orno/di.png?branch=master)](https://travis-ci.org/orno/di)

Orno\Di is a small but powerful dependency injection container that allows you to decouple components in your application in order to write clean and testable code. The container can automatically resolve dependencies of objects resolved through it.

See the [Wiki](https://github.com/orno/di/wiki) for full usage directions.

> ### Note
> Using a **Dependency Injection Container** is not the same as using the **Dependency Injection Pattern**. Be careful not to create a hard dependency on the container and be aware of the slight decline in performance it will create. Using Orno\Di correctly will allow you to create a good balance between fast, easy development of de-coupled, testable code and performance.

### Usage
- [Installation](#installation)
- [Factory Closures](#factory-closures)
- [Constructor Injection](#constructor-injection)
- [Setter Injection](#setter-injection)
- [Automatic Resolution of Dependencies](#automatic-resolution-of-dependencies)
- [Annotations](#annotations)

### Installation

Orno\Di is available on Packagist so the easiest way to install it into your project is via Composer. You han get more information about composer [here](http://getcomposer.org/doc/00-intro.md).

Simply add orno/di to your `composer.json` file like so:

    "require": {
        "orno/di": "v1.*"
    },

#### A Note on Versioning

It is recommended to use the above version string in your `composer.json` file as Orno components use a semantic versioning system that will never remove functionality from non full version releases.

### Factory Closures

The most performant way to use Orno\Di is to use factory closures/anonymous functions to build your objects. By registering a closure that returns a fully configured object, when resolved, your object will be lazy loaded as and when you need access to it.

Consider an object `Foo` that depends on another object `Bar`. The following will return an instance of `Foo` containing a member `bar` that contains an instance of `Bar`.

```php
class Foo
{
    public $bar;
    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
}

class Bar
{
    // ..
}

$container = new Orno\Di\Container;

$container->register('foo', function() {
    $bar = new Bar;
    return new Foo($bar);
});

$foo = $container->resolve('foo');
```

### Constructor Injection

The container can be used to register objects at run time and provide constructor arguments such as dependencies or config items. For example, if we have a `Session` object that depends on an implementation of a `StorageInterface` and also requires a session key string. We could do the following:

```php
class Session
{
    protected $storage;
    protected $sessionKey;
    public function __construct(StorageInterface $storage, $sessionKey)
    {
        $this->storage    = $storage;
        $this->sessionKey = $sessionKey;
    }
}

interface StorageInterface
{
    // ..
}

class Storage implements StorageInterface
{
    // ..
}

$container = new Orno\Di\Container;

$container->register('session', 'Session')
          ->withArguments([new Storage, 'my_session_key']);

$session = $container->resolve('storage');
```

### Setter Injection

If you prefer setter injection to constructor injection, a few minor alterations can be made to accomodate this.

```php
class Session
{
    protected $storage;
    protected $sessionKey;
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }
    public function setSessionKey($sessionKey)
    {
        $this->sessionKey = $sessionKey;
    }
}

interface StorageInterface
{
    // ..
}

class Storage implements StorageInterface
{
    // ..
}

$container = new Orno\Di\Container;

$container->register('session', 'Session')
          ->withMethodCall('setStorage', [new Storage])
          ->withMethodCall('setSessionKey', ['my_session_key']);

$session = $container->resolve('session');
```

This has the added benefit of being able to manipulate the behaviour of the object with optional setters. Only call the methods you need for this instance of the object.

### Automatic Resolution of Dependencies

Orno\Di has the power to automatically resolve your objects and all of their dependencies recursively by inspecting the type hints of your constructor arguments. Unfortunately, this method of resolution has a few small limitations but is great for smaller apps. First of all, you are limited to constructor injection and secondly, all injections **must** be objects.

```php
class Foo
{
    public $bar;
    public $baz;
    public function __construct(Bar $bar, Baz $baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }
}

class Bar
{
    public $bam;
    public function __construct(Bam $bam)
    {
        $this->bam = $bam;
    }
}

class Baz
{
    // ..
}

class Bam
{
    // ..
}
```

In the above code, `Foo` has 2 dependencies `Bar` and `Baz`, `Bar` has a further dependency of `Bam`. In a normal case you would have to do the following to return a fully configured instance of `Foo`.

```php
$bam = new Bam;
$baz = new Baz;
$bar = new Bar($bam);
$foo = new Foo($bar, $baz);
```

With nested dependencies, this can become quite cumbersome and hard to keep track of. With the container, to return a fully configured instance of `Foo` it is as simple as turning on auto resolution and requesting and instance of `Foo`.

```php
$container = (new Orno\Di\Container)->autoResolve(true);

$foo = $container->resolve('Foo');
```

### Annotations

When using automatic resolution, what happens when our requested object has a dependency that is an implementation of an interface? If we look back to our `Session` object in the *Constructor Injection* example, it requires an implementation of `StorageInterface`. With discreet annotations in your doc block it is easy to specify what implementation you want to inject.

```php
class Session
{
    protected $storage;

    /**
     * @param Storage $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }
}

interface StorageInterface
{
    // ..
}

class Storage implements StorageInterface
{
    // ..
}

$container = (new Orno\Di\Container)->autoResolve(true);

$session = $container->resolve('Session');
```

The container looks simply at the `@param` annotation so as to not force you to change the way you write your code. In this example the container sees that `$session` wants the object `Session` and injects it automatically.
