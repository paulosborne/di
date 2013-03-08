<?php namespace Tests;

use Orno\Di\Container;
use Orno\Di\Definition;
use Orno\Di\ContainerAwareTrait;
use PHPUnit_Framework_TestCase;
use stdClass;
use Assets\Foo;
use Assets\Bar;
use Assets\Baz;
use Assets\BazInterface;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    use ContainerAwareTrait;

    public function testClosureResolution()
    {
        Container::getContainer()->register('Test', function() {
            return 'Hello World';
        });

        $this->assertSame(Container::getContainer()->resolve('Test'), 'Hello World');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testDefinitionThrowsExceptionWhenNoClass()
    {
        $definition = new Definition(null, new Container);
        $definition();
    }

    public function testSetAndGetContainerWithTrait()
    {
        $this->assertTrue($this->getContainer() instanceof Container);
        $this->setContainer(new Container);
        $this->assertTrue($this->getContainer() instanceof Container);
    }

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
        $this->assertTrue($container->resolve('Assets\Baz') instanceof Baz);
    }

    public function testResolvesDependencyRegisteredWithContainer()
    {
        $container = (new Container)->autoResolve(true);
        $container->register('Assets\Bar');
        $container->register('Assets\Baz');
        $this->assertTrue($container->resolve('Assets\Foo') instanceof Foo);
    }

    public function testSharedResolution()
    {
        $container = (new Container)->autoResolve(true);
        $container->register('Baz', 'Assets\Baz', true);

        $object1 = $container->resolve('Baz');
        $object2 = $container->resolve('Baz');

        $this->assertSame($object1, $object2);
    }

    public function testClosureResolutionWithArgs()
    {
        $container = new Container;
        $container->register('Test', function($hello) {
            return $hello;
        });

        $this->assertSame($container->resolve('Test', ['Hello World']), 'Hello World');
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

        $this->assertTrue($container->resolve('Test') instanceof Baz);
        $this->assertTrue($container->resolve('Assets\BazInterface') instanceof Baz);
    }

    public function testMultipleNestedDependencies()
    {
        $container = (new Container)->autoResolve(true);

        $foo = $container->resolve('Assets\Foo');

        $this->assertTrue($foo instanceof Foo);
        $this->assertTrue($foo->bar instanceof Bar);
        $this->assertTrue($foo->bar->baz instanceof Baz);
    }

    public function testImplementationIsInstanceOfInterface()
    {
        $container = (new Container)->autoResolve(true);

        $this->assertTrue($container['Assets\Bar']->baz instanceof BazInterface);
    }

    public function testDefinitionInstanceConstructorInjection()
    {
        $container = new Container;

        $container->register('bar', 'Assets\Bar')
                  ->withArgument(new Baz);

        $bar = $container->resolve('bar');

        $this->assertTrue($bar instanceof Bar);
        $this->assertTrue($bar->baz instanceof Baz);
        $this->assertTrue($bar->baz instanceof BazInterface);
    }

    public function testDefinitionInstanceSetterInjection()
    {
        $container = new Container;

        $container->register('bar', 'Assets\Bar')
                  ->withMethodCall('setBaz', [new Baz]);

        $bar = $container->resolve('bar');

        $this->assertTrue($bar instanceof Bar);
        $this->assertTrue($bar->baz instanceof Baz);
        $this->assertTrue($bar->baz instanceof BazInterface);
    }

    public function testConstructorArgumentAsString()
    {
        $container = new Container;

        $container->register('baz', 'Assets\Baz');
        $container->register('bar', 'Assets\Bar')
                  ->withArgument('baz');

        $bar = $container->resolve('bar');

        $this->assertTrue($bar instanceof Bar);
        $this->assertTrue($bar->baz instanceof Baz);
        $this->assertTrue($bar->baz instanceof BazInterface);
    }

    public function testMethodArgumentsAsString()
    {
        $container = new Container;

        $container->register('baz', 'Assets\Baz');
        $container->register('bar', 'Assets\Bar')
                  ->withMethodCall('setBaz', ['baz']);

        $bar = $container->resolve('bar');

        $this->assertTrue($bar instanceof Bar);
        $this->assertTrue($bar->baz instanceof Baz);
        $this->assertTrue($bar->baz instanceof BazInterface);
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

        $this->assertTrue($foo instanceof Foo);
        $this->assertTrue($foo->bar instanceof Bar);
        $this->assertTrue($foo->bar->baz instanceof Baz);
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

        $this->assertTrue($bar instanceof Bar);
        $this->assertTrue($bar->baz instanceof Baz);
    }
}
