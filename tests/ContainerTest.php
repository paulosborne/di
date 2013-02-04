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

    public function testMultipleNestedDependencies()
    {
        $container = new Container;

        $this->assertTrue($container['Assets\Foo'] instanceof Assets\Foo);
        $this->assertTrue($container['Assets\Foo']->bar instanceof Assets\Bar);
        $this->assertTrue($container['Assets\Foo']->bar->baz instanceof Assets\Baz);
    }

    public function testImplementationIsInstanceOfInterface()
    {
        $container = new Container;

        $this->assertTrue($container['Assets\Bar']->baz instanceof Assets\BazInterface);
    }
}