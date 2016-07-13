<?php

use Phossa2\Di\Container;


require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/data1.php';

$container = new Container();

$cache = $container->get('MyCache');

var_dump($cache);
