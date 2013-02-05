# Dependency Injection

[![Buildi Status](https://travis-ci.org/orno/di.png?branch=master)](https://travis-ci.org/orno/di)

Orno\Di is a small but powerful dependency injection container that allows you to decouple components in your application in order to write clean and testable code.

- [Registering Objects](#registering-objects)
- [Aliasing Objects](#aliasing-objects)
- [Shared Objects](#shared-objects)
- [Resolving Items From the Container](#resolving-items-from-the-container)
- [Dependencies](#dependencies)
- [Implementing Interfaces](#implementing-interfaces)

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

### Resolving Items From the Container

Items can be resolved in two ways. By calling the `resolve` method or using `ArrayAccess` on the container object.

```php
$foo = $container->resolve('Foo');

$bar = $container['Bar'];
```

### Dependencies

Dependencies can be resolved by type hinting the object you want to inject in your classes constructor.

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
    public function helloWorld()
    {
        return 'Hello World!';
    }
}

$foo = $container->resolve('Foo');

$foo->bar->helloWorld();
```

The snippet above will assign the object `Foo` to `$foo` which will contain a property `$bar` containing the object `Bar`.

This could be extended and the container will resolve ALL nested dependencies.

### Implementations of Interfaces

What happens if the dependency you want to inject is an implementation of an interface? The container looks at your constructors `@param` annotations for a concrete implementation of the type hinted interface and injects that object.

```php
class Bar
{
    public $baz;

    /**
     * @param Baz $baz
     */
    public function __construct(BazInterface)
    {
        $this->baz = $baz;
    }
}

class Baz implements BazInterface
{
    public function helloWorld()
    {
        return 'Hello World!';
    }
}

interface BazInterface
{
    public function helloWorld();
}

$bar = $container->resolve('Bar');

$bar->baz->helloWorld();
```

Again, the above code will resolve all nested dependencies and assign the `Bar` object to `$bar` having the property `$baz` which will contain `Baz` a concrete implementation of `BazInterface`.