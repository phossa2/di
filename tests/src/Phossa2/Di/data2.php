<?php

use Phossa2\Di\Container;

return [
    // container class
    'di.class' => Container::getClassName(),

    // container service definitions
    'di.service' => [
        // cache service
        'cache'  => ['class' => 'MyCache', 'args' => ['${#driver}']],

        // cache driver service
        'driver' => 'MyCacheDriver',
    ],

    // interface/classname mappings
    'di.mapping' => [
    ],

    // init methods to run after container created
    'di.init' => [
        // different sections
        'default' => [],

        // mystuff section
        'mystuff' => [],
      ],
  ];
