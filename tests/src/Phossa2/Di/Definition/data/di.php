<?php

use Phossa2\Config\Delegator;
use Phossa2\Config\Config;

return [
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
    'mapping' => [
        'AnInterface' => 'AClass',
    ],
    'init' => [

    ]
];
