<?php namespace Orno\Di;

interface ContainerInterface
{
    public function register($alias, $object = null, $shared = false);

    public function resolve($alias);
}
