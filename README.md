# phossa2/di
[![Build Status](https://travis-ci.org/phossa2/di.svg?branch=master)](https://travis-ci.org/phossa2/di)
[![Code Quality](https://scrutinizer-ci.com/g/phossa2/di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phossa2/di/)
[![PHP 7 ready](http://php7ready.timesplinter.ch/phossa2/di/master/badge.svg)](https://travis-ci.org/phossa2/di)
[![HHVM](https://img.shields.io/hhvm/phossa2/di.svg?style=flat)](http://hhvm.h4cc.de/package/phossa2/di)
[![Latest Stable Version](https://img.shields.io/packagist/vpre/phossa2/di.svg?style=flat)](https://packagist.org/packages/phossa2/di)
[![License](https://poser.pugx.org/phossa2/di/license)](http://mit-license.org/)

**phossa2/di** is a *fast* and *powerful* [Container-Interop][Interop] implementation
of dependency injection library for PHP. It builds upon the versatile
[phossa2/config][config] library and supports [autowiring](#auto),
[container delegation](#delegate), [object decorating](#decorate),
[object scope](#scope) and more.

It requires PHP 5.4, supports PHP 7.0+ and HHVM. It is compliant with
[PSR-1][PSR-1], [PSR-2][PSR-2], [PSR-4][PSR-4], and coming [PSR-5][PSR-5],
[PSR-11][PSR-11].

[PSR-1]: http://www.php-fig.org/psr/psr-1/ "PSR-1: Basic Coding Standard"
[PSR-2]: http://www.php-fig.org/psr/psr-2/ "PSR-2: Coding Style Guide"
[PSR-4]: http://www.php-fig.org/psr/psr-4/ "PSR-4: Autoloader"
[PSR-5]: https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md "PSR-5: PHPDoc"
[PSR-11]: https://github.com/container-interop/fig-standards/blob/master/proposed/container.md "Container interface"
[Interop]: https://github.com/container-interop/container-interop "Container-Interop"
[config]: https://github.com/phossa2/config "phossa2/config"

Installation
---
Install via the `composer` utility.

```
composer require "phossa2/di=2.*"
```

or add the following lines to your `composer.json`

```json
{
    "require": {
       "phossa2/di": "^2.0.0"
    }
}
```

Usage
---

- With *autowiring* of classes.

  A couple of predefined simple classes as follows,

  ```php
  // file cache.php
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
  ```

  Get the `MyCache` instance automatically using the DI container,

  ```php
  use Phossa2\Di\Container;

  // should be aware of these classes
  require_once __DIR__ . '/cache.php';

  // create the container
  $container = new Container();

   // 'MyCache' classname as the service id
  if ($container->has('MyCache')) {
      $cache = $container->get('MyCache');
  }
  ```

  With [autowiring](#auto) is turned on by default, the container will look for the
  `MyCache` class if no service defined as 'MyCache', and resolves its dependency
  injection automatically when creating the `MyCache` instance.

- With manual service addition using `set()`

  Services can be added to the container manually by using `set()` method.

  ```php
  use Phossa2\Di\Container;

  // should be aware of these classes
  require_once __DIR__ . '/cache.php';

  // create the container
  $container = new Container();

  // add service with id 'cache'
  $container->set('cache', [
      'class' => 'MyCache', // classname
      'args'  => ['${#driver}'] // constructor arguments
  ]);

  // add service 'driver' with a callback
  $container->set('driver', function() {
      return new \MyCacheDriver();
  });

  // get the service
  var_dump($container->get('cache') instanceof \MyCache); // true
  ```

  A service reference `'${#driver}'` used in the constructor arguments here
  indicating it is the `driver` service (object).

- With configuration from files or data array

  Container may use a `Phossa2\Config\Config` instance as its definitions for
  services. The `Phossa2\Config\Config` instance may either read configs from files
  or get configs from a data array as follows,

  ```php
  use Phossa2\Di\Container;
  use Phossa2\Config\Config;

  $configData = [
      // container class
      'di.class' => 'Phossa2\\Di\\Container',

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

  // create $config instance with provided data
  $config = new Config(null, null, $configData);

  // instantiate container with $config instance with base node is 'di'
  $container = new Container($config, 'di');

  // get service by id 'cache' (di.service.cache)
  $cache = $container->get('cache');

  // true
  var_dump($cache instanceof \MyCache);
  ```

  By default, container related configurations are under the node `di` and service
  definitions are under the `di.service` node of the `$config` instance.

Features
---

- <a name="ref"></a>**References**

  References in the format of '${reference}' can be used to refer to predefined
  parameters from the config or services in the container.

  - Parameter references

    See [reference](https://github.com/phossa2/config#ref) for detail. Parameter
    references are read from configuration files or can be defined by `param()` as
    follows,

    ```php
    // define a new parameter
    $container->param('cache.dir', '${system.tmpdir}/cache');

    // use the cache.dir parameter
    $container->set('cache', [
        'class' => '${cache.class}', // predefined in file
        'args'  => ['${cache.dir}']  // just defined before
    ]);
    ```

  - Service references

    Service reference in the format of '${#service_id}' can be used to referring
    a service in the container (or in the [delegator](#delegate)).

    ```php
    $container->set('cache', [
        'class' => '${cache.class}',
        'args'  => ['${#cache_driver}'] // service reference
    ]);
    ```

    Two *reserved* service references are '${#container}' and '${#config}'. These
    two are refering the container instance itself and the config instance it is
    using. These two can be used just like other service references.

  - Using references

    References can be used anywhere in the configs or as the arguments for all
    container methods(except for the paramter `$id` of the method).

    ```php
    // log a warning message
    $container->run(['${#logger}', 'warning'], ['warning from ${log.facility}']);

    // resolve references
    $data = ['${system.dir}', '${#logger}'];
    $container->resolve($data); // all references in $data are resolved now
    ```

- <a name="auto"></a>**Autowiring and mapping**

  *Autowiring* is the ability of container instantiating objects and resolving its
  dependencies automatically by their classname or interface name. The base for
  autowiring is the PHP function parameter *type-hinting*.

  By reflecting on class, constructor and methods, *phossa2/di* is able to find
  the right class for the instance (user need to use the classname as the service
  id) and right classes for its dependencies (type-hinted with the classnames).

  If interface name is used for dependency type-hint, users may set up the mapping
  of interfaces to the right classnames as follows,

  ```php
  // map an interface to a classname
  $container->map(
      'Phossa2\\Cache\\CachePoolInterface', // MUST NO leading backslash
      'Phossa2\\Cache\\CachePool'
  );

  // map an interface to a service id reference
  $container->map('Phossa\\Cache\\CachePoolInterface', '${#cache}');

  // map an interface to a parameter reference
  $container->map('Phossa\\Cache\\CachePoolInterface', '${cache.class}');

  // map an interface to a callback
  $container->map('Phossa\\Cache\\CachePoolInterface', function() {
      return new \Phossa2\Cache\CachePool();
  });
  ```

  Or define mappings in the config node `di.mapping` as follows,

  ```php
  $configData = [
      // ...
      'di.mapping' => [
          'Phossa\\Cache\\CachePoolInterface' => '${cache.class}',
          // ...
      ],
      // ...
  ];
  ```

  Autowiring can be turned on/off. Turn off autowiring will enable user to check
  any defintion errors without automatic loading.

  ```php
  // turn off auto wiring
  $container->auto(false);

  // turn on auto wiring
  $container->auto(true);
  ```

- <a name="decorate"></a>**Object decorating**

  *Object decorating* is to apply decorating changes (run methods etc.) right
  after the instantiation of a service instance.

  - Decorating methods for *individual instance* only

    ```php
    $container->set('cache', [
        'class'   => 'Phossa2\\Cache\\Cache',
        'args'    => ['${#cachedriver}'], // constructor arguments
        'methods' => [
            ['clearCache'], // cache method with no arguments
            ['setLogger', ['${#logger}']], // method with arguments
            [[$logger, 'setLabel'], ['cache_label']], // callable with arguments
            [['${#driver}, 'init']], // pseduo callable
            // ...
        ],
    ]);
    ```

    By adding `methods` section into the `cache` service definition in the format of
    `[ callableOrMethodName, OptionalArgumentArray ]`, these methods will be executed
    right after `cache` instantiation.

    `callableOrMethodName` here can be,

    - method name of the current instance

    - a valid callable

    - a psedudo callable with references. After resolving the references, it is a
      valid callable.

    `OptionalArgumentArray` here can be,

    - empty

    - array of values or references

  - Common decorating methods for *all instances*

    ```php
    $configData = [
        // common methods for all instances
        'di.common' => [
            // interface name and method
            ['Psr\\Log\\LoggerAwareInterface', ['setLogger', ['${#logger}']]],

            // tester callable and method
            [
                function($object, $container) {
                    return $object instanceof 'Psr\\Log\\LoggerAwareInterface'
                },
                ['setLogger', ['${#logger}']]
            ],
        ],
    ];
    ```

    Common methods can be configured in the 'di.common' node to apply to all the
    instances right after their instantiation. The definition consists of two parts,
    the first is an interface/classname or a callable takes current instance and
    the container as parameters. The second part is in the same method format
    mentioned before.

    To skip execution of common methods for one service, define it with `skip` as
    follows,

    ```php
    $container->set('logger', [
        'class' => 'Phossa2\Logger\Logger',
        'skip'  => true
    ]);
    ```

APIs
---

- <a name="api"></a>ConfigInterface API


- <a name="other"></a>Other public methods

  - Writable related

    - `setWritable(bool $writable)`

      Enable or disable the `set()` functionality.

    - `isWritable(): bool`

      Test to see if config writable.

  - Reference related

    - `setReferencePattern($start, $end)`

      Reset the reference start chars and ending chars. The default are `'${'` and
      `'}'`

    - `hasReference($string): bool`

      Test to see if there are references in the `$string`

    - `deReference($string): mixed`

      Dereference all the references in the `$string`. The result might be `string`,
      `array` or even `object`.

    - `deReferenceArray(&$data)`

      Recursively dereference everything in the `$data`. `$data` might be `string`
      or `array`. Other input will be untouched.

Dependencies
---

- PHP >= 5.4.0

- phossa2/shared >= 2.0.9

License
---

[MIT License](http://mit-license.org/)
