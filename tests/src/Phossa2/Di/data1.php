<?php

class MyCache
{
    private $driver;

    public function __construct(MyCacheDriver $driver)
    {
        $this->driver = $driver;
    }

    public function getDriver() {
        return $this->driver;
    }

    public function runMethod1() {
        echo "runMethod1_";
    }

    public function echoIt($str) {
        echo $str;
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

class A {
    private $b, $c;

    public function __construct(B $b, C $c) {
        $this->b = $b;
        $this->c = $c;
    }

    public function getB() {
        return $this->b;
    }

    public function getC() {
        return $this->c;
    }
}

class B {
    private $c;
    public function __construct(C $c) {
        $this->c = $c;
    }

    public function getC() {
        return $this->c;
    }
}

class C {
}

