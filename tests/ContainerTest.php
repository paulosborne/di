<?php

include __DIR__ . '/../src/Orno/Di/Container.php';
include 'assets/Foo.php';
include 'assets/Bar.php';
include 'assets/BazInterface.php';
include 'assets/Baz.php';

use Orno\Di\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    public function testArrayAccess()
    {
        $container = new Container;
        $container['test'] = function() { return 'Hello World'; };
        $this->assertTrue(isset($container['test']));
        $this->assertSame($container['test'], 'Hello World');
        unset($container['test']);
        $this->assertFalse(isset($container['test']));
    }

    public function testAutomaticResolution()
    {
        $container = new Container;
        $this->assertTrue($container->resolve('Assets\Baz') instanceof Assets\Baz);
    }

    public function testSharedResolution()
    {
        $container = new Container;
        $container->register('Baz', 'Assets\Baz', true);

        $object1 = $container->resolve('Baz');
        $object2 = $container->resolve('Baz');

        $this->assertSame($object1, $object2);
    }

    public function testClosureResolution()
    {
        $container = new Container;
        $container->register('Test', function() {
            return 'Hello World';
        });

        $this->assertSame($container->resolve('Test'), 'Hello World');
    }

    public function testSharedClosureResolution()
    {
        $container = new Container;
        $container->register('Baz', function() {
            return new stdClass;
        }, true);

        $object1 = $container->resolve('Baz');
        $object2 = $container->resolve('Baz');

        $this->assertSame($object1, $object2);
    }

    public function testAliasedDependencyResolution()
    {
        $container = new Container;

        $container->register('Test', 'Assets\Baz');
        $container->register('Assets\BazInterface', 'Assets\Baz');

        $this->assertTrue($container->resolve('Test') instanceof Assets\Baz);
        $this->assertTrue($container->resolve('Assets\BazInterface') instanceof Assets\Baz);
    }

    public function testMultipleNestedDependencies()
    {
        $container = new Container;

        $foo = $container->resolve('Assets\Foo');

        $this->assertTrue($foo instanceof Assets\Foo);
        $this->assertTrue($foo->bar instanceof Assets\Bar);
        $this->assertTrue($foo->bar->baz instanceof Assets\Baz);
    }

    public function testImplementationIsInstanceOfInterface()
    {
        $container = new Container;

        $this->assertTrue($container['Assets\Bar']->baz instanceof Assets\BazInterface);
    }
}