<?php

class MyCache
{
    private $driver;

    public function __construct(MyCacheDriver $driver)
    {
        $this->driver = $driver;
    }

    // ...
    public function runMethod1() {
        echo "runMethod1_";
    }
}

class MyCacheDriver implements DriverInterface
{
    // ...
    public function driverMethod() {
        echo "driverMethod_";
    }
}

interface DriverInterface {
    // ...
}

class YourCache
{
    private $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    // ...
}
