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

class MyCacheDriver
{
    // ...
}
