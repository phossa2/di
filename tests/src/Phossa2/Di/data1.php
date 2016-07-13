<?php

class MyCache
{
    private $driver;

    public function __construct(MyCacheDriver $driver)
    {
        $this->driver = $driver;
    }

    // ...
}

class MyCacheDriver implements DriverInterface
{
    // ...
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
