# Orno\Di - PHP Dependency Injection Container

[![Buildi Status](https://travis-ci.org/orno/di.png?branch=master)](https://travis-ci.org/orno/di)

Orno\Di is a small but powerful dependency injection container that allows you to decouple components in your application in order to write clean and testable code. The container can automatically resolve dependencies of objects resolved through it.

> # Note
> Using a **Dependency Injection Container** is not the same as using the **Dependency Injection Pattern**. Be careful not to create a hard dependency on the container and be aware of the slight decline in performance it will create. Using Orno\Di correctly will allow you to create a good balance between fast, easy development of de-coupled, testable code and performance.

- [Basic Usage](#basic-usage)

### Basic Usage

The most performant way to use Orno\Di is to use factory closures/anonymous functions to build your objects. By registering a closure that returns a fully configured object, when resolved, your object will be lazy loaded as and when you need access to it.

    class Foo
    {
        public $bar;
        public function __construct(Bar $bar)
        {
            $this->bar = $bar
        }
    }

    class Bar
    {
        // ..
    }

    $container = new Orno\Di\Container;

    $container->register(function() {
        $bar = new Bar;
        return new Foo($bar);
    });


