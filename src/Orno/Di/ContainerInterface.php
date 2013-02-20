<?php namespace Orno\Di;

interface ContainerInterface
{
    public function register();

    public function resolve();
}
