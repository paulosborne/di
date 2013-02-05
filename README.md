# Dependency Injection

[![Buildi Status](https://travis-ci.org/orno/di.png?branch=master)](https://travis-ci.org/orno/di)

Orno\Di is a small but powerful dependency injection container that allows you to decouple components in your application in order to write clean and testable code.

- [Registering Objects](#registering-objects)
- [Aliasing Objects](#aliasing-objects)
- [Shared Objects](#shared-objects)

### Registering Objects

Objects can be defined as anonymous functions or a string of the fully qualified namespace.

```php
$container = new Orno\Di\Container;

$container->register('Foo', function() {
    return new Foo;
});

$container->register('Bar');
```

### Aliasing Objects

If your object is a fully qualified namespace, you can easily alias the object to a shorter name for easier access when resolving.

```php
$container->register('Foo', 'Full\Namespace\Foo')
```

### Shared Objects

Objects can be registered as singletons/shared objects by passing `true` as a third parameter to the `register` method. The same can be done with an anonymous function and the container will save a shared definition of that closure.

```php
$container->register('Foo', 'Full\Namespace\Foo', true);

$container->register('Bar', function() {
    return new Bar;
}, true);
```


