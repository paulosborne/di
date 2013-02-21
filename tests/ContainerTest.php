<?php

include __DIR__ . '/../src/Orno/Di/ContainerInterface.php';
include __DIR__ . '/../src/Orno/Di/Container.php';
include __DIR__ . '/../src/Orno/Di/ContainerAwareTrait.php';
include __DIR__ . '/../src/Orno/Di/Definition.php';
include 'assets/Foo.php';
include 'assets/Bar.php';
include 'assets/BazInterface.php';
include 'assets/Baz.php';

use Orno\Di\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    public function testArrayAccess()
    {
        $container = (new Container)->autoResolve(true);
        $container['test'] = function() { return 'Hello World'; };
        $this->assertTrue(isset($container['test']));
        $this->assertSame($container['test'], 'Hello World');
        unset($container['test']);
        $this->assertFalse(isset($container['test']));
    }

    public function testAutomaticResolution()
    {
        $container = (new Container)->autoResolve(true);
        $this->assertTrue($container->resolve('Assets\Baz') instanceof Assets\Baz);
    }

    public function testSharedResolution()
    {
        $container = (new Container)->autoResolve(true);
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
        $container = (new Container)->autoResolve(true);

        $container->register('Test', 'Assets\Baz');
        $container->register('Assets\BazInterface', 'Assets\Baz');

        $this->assertTrue($container->resolve('Test') instanceof Assets\Baz);
        $this->assertTrue($container->resolve('Assets\BazInterface') instanceof Assets\Baz);
    }

    public function testMultipleNestedDependencies()
    {
        $container = (new Container)->autoResolve(true);

        $foo = $container->resolve('Assets\Foo');

        $this->assertTrue($foo instanceof Assets\Foo);
        $this->assertTrue($foo->bar instanceof Assets\Bar);
        $this->assertTrue($foo->bar->baz instanceof Assets\Baz);
    }

    public function testImplementationIsInstanceOfInterface()
    {
        $container = (new Container)->autoResolve(true);

        $this->assertTrue($container['Assets\Bar']->baz instanceof Assets\BazInterface);
    }

    public function testDefinitionInstanceConstructorInjection()
    {
        $container = new Container;

        $container->register('bar', 'Assets\Bar')
                  ->withArgument(new Assets\Baz);

        $bar = $container->resolve('bar');

        $this->assertTrue($bar instanceof Assets\Bar);
        $this->assertTrue($bar->baz instanceof Assets\Baz);
        $this->assertTrue($bar->baz instanceof Assets\BazInterface);
    }

    public function testDefinitionInstanceSetterInjection()
    {
        $container = new Container;

        $container->register('bar', 'Assets\Bar')
                  ->withMethodCall('setBaz', [new Assets\Baz]);

        $bar = $container->resolve('bar');

        $this->assertTrue($bar instanceof Assets\Bar);
        $this->assertTrue($bar->baz instanceof Assets\Baz);
        $this->assertTrue($bar->baz instanceof Assets\BazInterface);
    }

    public function testConstructorArgumentAsString()
    {
        $container = new Container;

        $container->register('baz', 'Assets\Baz');
        $container->register('bar', 'Assets\Bar')
                  ->withArgument('baz');

        $bar = $container->resolve('bar');

        $this->assertTrue($bar instanceof Assets\Bar);
        $this->assertTrue($bar->baz instanceof Assets\Baz);
        $this->assertTrue($bar->baz instanceof Assets\BazInterface);
    }

    public function testMethodArgumentsAsString()
    {
        $container = new Container;

        $container->register('baz', 'Assets\Baz');
        $container->register('bar', 'Assets\Bar')
                  ->withMethodCall('setBaz', ['baz']);

        $bar = $container->resolve('bar');

        $this->assertTrue($bar instanceof Assets\Bar);
        $this->assertTrue($bar->baz instanceof Assets\Baz);
        $this->assertTrue($bar->baz instanceof Assets\BazInterface);
    }

    public function testSetConfigWithConstructorInjection()
    {
        $map = [
            'Assets\Foo' => [
                'arguments' => ['Assets\Bar']
            ],
            'Assets\Bar' => [
                'arguments' => ['Assets\Baz']
            ],
            'Assets\Baz' => []
        ];

        $container = new Container($map);

        $foo = $container->resolve('Assets\Foo');

        $this->assertTrue($foo instanceof Assets\Foo);
        $this->assertTrue($foo->bar instanceof Assets\Bar);
        $this->assertTrue($foo->bar->baz instanceof Assets\Baz);
    }

    public function testSetConfigWithSetterInjection()
    {
        $map = [
            'Assets\Bar' => [
                'methods' => [
                    'setBaz' => [
                        'Assets\Baz'
                    ]
                ]
            ],
            'Assets\Baz' => []
        ];

        $container = new Container($map);

        $bar = $container->resolve('Assets\Bar');

        $this->assertTrue($bar instanceof Assets\Bar);
        $this->assertTrue($bar->baz instanceof Assets\Baz);
    }
}
