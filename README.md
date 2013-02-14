# Orno\Di - PHP Dependency Injection Container

[![Buildi Status](https://travis-ci.org/orno/di.png?branch=master)](https://travis-ci.org/orno/di)

Orno\Di is a small but powerful dependency injection container that allows you to decouple components in your application in order to write clean and testable code. The container can automatically resolve dependencies of objects resolved through it.

> ### Note
> Using a **Dependency Injection Container** is not the same as using the **Dependency Injection Pattern**. Be careful not to create a hard dependency on the container and be aware of the slight decline in performance it will create. Using Orno\Di correctly will allow you to create a good balance between fast, easy development of de-coupled, testable code and performance.

### Usage
- [Factory Closures](#factory-closures)
- [Setting Constructor Arguments](#setting-constructor-arguments)

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

### Setting Constructor Arguments

The container can be used to register objects at run time and provide constructor arguments such as dependencies or config items. For example, if we have a `Session` object that depends on an implementation of a `StorageInterface` and also requires a session key string. We could do the following:

```php
class Session
{
    protect $storage;
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
          ->withArguments([
            new Storage,
            'my_session_key'
          ]);

$session = $container->resolve('storage');
```