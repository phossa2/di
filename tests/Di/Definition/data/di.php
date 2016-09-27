<?php

use Phossa2\Config\Delegator;
use Phossa2\Config\Config;

return [
    // service definitions
    'service' => [
        'delegator' => [
            'class' => Delegator::getClassName(),
            'args'  => [],
        ],
        'config' => [
            'class' => Config::getClassName(),
        ],
        'delegator2' => [
            'class' => '${di.service.delegator.class}',
        ]
    ],

    // interface to classname mapping etc.
    'mapping' => [
        'AnInterface' => 'AClass',
    ],

    // initial methods to run after $container created
    'init' => [

    ],

    // common methods to run after an instance created
    'common' => [

    ],
];
