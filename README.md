# phossa2/di
[![Build Status](https://travis-ci.org/phossa2/di.svg?branch=master)](https://travis-ci.org/phossa2/di)
[![Code Quality](https://scrutinizer-ci.com/g/phossa2/di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phossa2/di/)
[![PHP 7 ready](http://php7ready.timesplinter.ch/phossa2/di/master/badge.svg)](https://travis-ci.org/phossa2/di)
[![HHVM](https://img.shields.io/hhvm/phossa2/di.svg?style=flat)](http://hhvm.h4cc.de/package/phossa2/di)
[![Latest Stable Version](https://img.shields.io/packagist/vpre/phossa2/di.svg?style=flat)](https://packagist.org/packages/phossa2/di)
[![License](https://poser.pugx.org/phossa2/di/license)](http://mit-license.org/)

**phossa2/di** is a *fast* and *powerful* [Container-Interop][Interop]
implementation of dependency injection library for PHP. It builds upon the
versatile [phossa2/config][config] library and supports [autowiring](#auto),
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

- With [*autowiring*](#auto) of classes.

  A couple of simple classes as follows,

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

  Instead of creating `MyCacheDriver` and `MyCache` instances manually, you may
  get the both instances automatically using the DI container,

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

  With [autowiring](#auto) is turned on by default, the container will look for
  the `MyCache` class if no service defined as 'MyCache', and resolves its
  dependency injection (here, is the `MyCacheDriver` instance) automatically
  when creating the `MyCache` instance.

- With manual service addition using `set()`

  Services can be added to the container manually by using `set()` method.

  ```php
  use Phossa2\Di\Container;

  // should be aware of these classes
  require_once __DIR__ . '/cache.php';

  // create the container
  $container = new Container();

  // turn off autowiring
  $container->auto(false);

  // define service with an array
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
  indicating it is the `driver` service instance from the container.

- With configuration from files or array

  Container may use a `Phossa2\Config\Config` instance as its definition
  resolver for both parameters and services. The `Phossa2\Config\Config`
  instance may either read configs from files or get configs from an array as
  follows,

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

      // interface to classname mappings
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

  // instantiate container with $config instance and definition base node 'di'
  $container = new Container($config, 'di');

  // get service by id 'cache' (di.service.cache)
  $cache = $container->get('cache');

  // true
  var_dump($cache instanceof \MyCache);
  ```

  By default, container related configurations are under the node `di` and
  service definitions are under the `di.service` node.

Features
---

- <a name="ref"></a>**References**

  References in the format of '${reference}' can be used to refer to predefined
  parameters from the config or services in the container.

  - Parameter references

    See [phossa2/config reference](https://github.com/phossa2/config#ref) for
    detail. Parameter references are read from configuration files or can be
    defined by container method `param()` as follows,

    ```php
    // define a new parameter for the container
    $container->param('cache.dir', '${system.tmpdir}/cache');

    // use the cache.dir parameter defined above
    $container->set('cache', [
        'class' => '${cache.class}', // predefined in file
        'args'  => ['${cache.dir}']  // just defined before
    ]);
    ```

  - Service references

    Service reference in the format of '${#service_id}' can be used to referring
    a service instance in the container (or in the [delegator](#delegate)).

    ```php
    $container->set('cache', [
        'class' => '${cache.class}',
        'args'  => ['${#cache_driver}'] // service reference
    ]);
    ```

    **Two reserved service references are '${#container}' and '${#config}'**.
    These two are refering the container instance itself and the config instance
    it is using. These two can be used just like other service references.

  - Using references

    References can be used anywhere in the configs or as the arguments for all
    container methods (except for the paramter `$id` of the methods).

    ```php
    // run(callable, arguments) with references
    $container->run(['${#logger}', 'warning'], ['warning from ${log.facility}']);

    // resolve references
    $data = ['${system.dir}', '${#logger}'];
    $container->resolve($data); // all references in $data are now resolved
    ```

- <a name="auto"></a>**Autowiring and mapping**

  *Autowiring* is the ability of container instantiating objects and resolving
  its dependencies automatically by their classname or interface name. The base
  for autowiring is the PHP function parameter *type-hinting*.

  By reflecting on class, constructor and methods, *phossa2/di* is able to find
  the right class for the instance (user need to use the classname as the service
  id) and right classes for its dependencies (type-hinted with the classnames).

  If interface name is used for dependency type-hint, users may set up the
  mapping of interfaces to the right classnames as follows,

  ```php
  // map an interface to a classname
  $container->map(
      'Phossa2\\Cache\\CachePoolInterface', // MUST NO leading backslash
      'Phossa2\\Cache\\CachePool'
  );

  // map an interface to a service id reference
  $container->map('Phossa2\\Cache\\CachePoolInterface', '${#cache}');

  // map an interface to a parameter reference
  $container->map('Phossa2\\Cache\\CachePoolInterface', '${cache.class}');

  // map an interface to a callback
  $container->map('Phossa2\\Cache\\CachePoolInterface', function() {
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

  Autowiring can be turned on/off. Turning off autowiring will enable user to
  check any defintion errors without automatic loading.

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

    By adding `methods` section into the `cache` service definition in the
    format of `[ callableOrMethodName, OptionalArgumentArray ]`, these methods
    will be executed right after `cache` instantiation.

    `callableOrMethodName` here can be,

    - method name of the service instance

    - a valid callable

    - a psedudo callable with references (after resolving the references, it is
      a valid callable).

    `OptionalArgumentArray` here can be,

    - empty

    - array of values or references

  - Common decorating methods for *all instances*

    ```php
    $configData = [
        // common methods for all instances
        'di.common' => [
            // [ tester(): bool, method ]
            [function($object, $container) {
                return $object instanceof 'Psr\\Log\\LoggerAwareInterface'
             }, ['setLogger', ['${#logger}']]],
        ],
    ];
    ```

    Common methods can be configured in the 'di.common' node to apply to all the
    instances right after their instantiation. The definition consists of two
    parts, the first is a tester callable takes current instance and the
    container as parameters and returns a boolean value. The second part is in
    the same method format as in the service definition 'methods'.

    To skip execution of common methods for one service, define it with `skip`
    as follows,

    ```php
    $container->set('logger', [
        'class' => 'Phossa2\Logger\Logger',
        'skip'  => true
    ]);
    ```

- <a name="delegate"></a>**Container delegation**

  According to [Interop Container Delegate Lookup](https://github.com/container-interop/fig-standards/blob/master/proposed/container.md),
  container may register a delegate container (the delegator), and

  - Calls to the `get()` method should only return an entry if the entry is
    part of the container. If the entry is not part of the container, an
    exception should be thrown (as requested by the `ContainerInterface`).

  - Calls to the `has()` method should only return true if the entry is part
    of the container. If the entry is not part of the container, false should
    be returned.

  - If the fetched entry has dependencies, **instead** of performing the
    dependency lookup in the container, the lookup is performed on the
    delegate container (delegator).

  - **Important** By default, the lookup *SHOULD* be performed on the delegate
    container only, not on the container itself.

  This library fully supports the delegate feature.

  ```php
  use Phossa2\Di\Delegator;

  // create delegator
  $delegator = new Delegator();

  // create container
  $container = new Container();

  // insert container into delegator
  $delegator->addContainer($container);

  // get from delegator now
  $cache = $delegator->get('cache');
  ```

- <a name="scope"></a>**Object scope**

  - Shared or single scope

    By default, service instances in the container are shared inside the
    container. Actually they have the scope of `Container::SCOPE_SHARED`. If
    users want different instance each time, they may either use the method
    `one()` or define the service with `Container::SCOPE_SINGLE` scope.

    ```php
    // cache service by default is in shared scope
    $cache1 = $container->get('cache');

    // get again
    $cache2 = $container->get('cache');

    // same
    var_dump($cache1 === $cache2); // true

    // a new cache instance with 'one()'
    $cache3 = $container->one('cache');

    // different instances
    var_dump($cache1 !== $cache3); // true

    // but both share the same cacheDriver dependent service
    var_dump($cache1->getDriver() === $cache3->getDriver()); // true
    ```

    Or define the service as `Container::SCOPE_SINGLE`

    ```php
    $container->set('cache', [
        'class' => '\\Phossa2\\Cache\\CachePool'),
        'scope' => Container::SCOPE_SINGLE
    ]);

    // each get() will return a new cache
    $cache1 = $container->get('cache');
    $cache2 = $container->get('cache');

    // different instances
    var_dump($cache1 !== $cache2); // true

    // dependent service are shared
    var_dump($cache1->getDriver() === $cache->getDriver()); // true
    ```

    Set the container's default scope to `Container::SCOPE_SINGLE` will cause
    each `get()` returns a new instance (unless 'scope' is explicitly defined
    as shared for this service).

    ```php
    // set default scope to SCOPE_SINGLE
    $container->share(false);

    // a new copy of cache service
    $cache1 = $container->get('cache');

    // another new cache service
    $cache2 = $container->get('cache');

    // different instances
    var_dump($cache1 !== $cache2); // true

    // dependencies are different
    var_dump($cache1->getDriver() === $cache->getDriver()); // false
    ```

  - Use your own scope

    You may get an instance in your own scope as follows no matter whatever the
    default scope or the defined scope for this instance,

    ```php
    // instance in scope 'myScope'
    $cacheOfMyScope = $container->get('cache@myScope');

    // new instance in single scope, even though you specified one
    $cacheOfSingle = $container->one('cache@myScope');

    // instance in shared scope
    $cache = $container->get('cache');
    ```

    Service references can also have scope defined as follows,

    ```php
    $container->set('cache', [
        'class' => 'Phossa2\\Cache\\Cache',
        'args'  => ['${#driver@myScope}'] // use driver of myScope
    ]);
    ```

    **NOTE**: Service id with scope appended has the highest priority as of
    scope over defined scope for this service, and over default scope of the
    container.

  - Share instance only in certain object

    Sometimes, user may want to share one instance only inside certain object.

    ```php
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
    }

    class C {
    }

    // an instance of A
    $a1 = $container->one('A');

    // another instance of A
    $a2 = $container->one('A');

    // $a1 and $a2 is different
    var_dump($a1 !== $a2); // true

    // C is the same under A
    var_dump($a1->getC() === $a1->getB()->getC()); // true

    // C is also shared among different A
    var_dump($a1->getC() === $a2->getC()); // true
    ```

    In previous code, `C` is not only shared under the `A`, but also shared
    among different instances of `A`. What if user want to share `C` only under
    the `A` but not between `A` ?

    By setting scope of `C` to '#A' as follows,

    ```php
    // this scope only takes effect when under service A
    $container->set('C', [ 'class' => 'C', 'scope' => '#A']);

    // an instance of A
    $a1 = $container->one('A');

    // another instance of A
    $a2 = $container->one('A');

    // C is different among different A
    var_dump($a1->getC() !== $a2->getC()); // true

    // C is same under one A
    var_dump($a1->getC() === $a1->getB()->getC()); // true
    ```

- <a name="array"></a>**Array access and read only**

  Both `Phossa2\Di\Container` and `Phossa2\Di\Delegator` implements
  `\ArrayAccess` interface.

  ```php
  $container = new Container();
  $delegator = new Delegator();

  $delegator->addContainer($container);

  // equals to $delegator->has('A')
  if (isset($delegator['A'])) {
      var_dump($delegator['A'] === $container['A']); // true
  }
  ```

  By default `Phossa2\Di\Container` is writable which means user can add new
  service definitions to the container manually by using `set()`.

  To get a readonly container,

  ```php
  $container = new Container();
  $container->setWritable(false);

  var_dump($container->isWritable()); // false

  // delegator also
  $delegator = new Delegator();
  $delegator->setWritable(false);

  var_dump($delegator->isWritable()); // false
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

      Reset the reference start chars and ending chars. The default are `'${'`
      and `'}'`

    - `hasReference($string): bool`

      Test to see if there are references in the `$string`

    - `deReference($string): mixed`

      Dereference all the references in the `$string`. The result might be
      `string`, `array` or even `object`.

    - `deReferenceArray(&$data)`

      Recursively dereference everything in the `$data`. `$data` might be
      `string` or `array`. Other input will be untouched.

Dependencies
---

- PHP >= 5.4.0

- phossa2/config >= 2.0.11

- phossa2/shared >= 2.0.19

License
---

[MIT License](http://mit-license.org/)
