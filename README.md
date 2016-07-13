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
[container delegation](#delegate), [configuration delegation](#confdel),
[object decorating](#decorate), [object scope](#scope) and more.

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

  Get the `MyCache` instance using the DI container automatically,

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

  // turn off autowiring
  $container->getResolver()->autoWiring(false);

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
  indicating it is `driver` service (object).

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

  // now get by defined service id 'cache' (di.service.cache)
  $cache = $container->get('cache');

  // true
  var_dump($cache instanceof \MyCache);
  ```

  By default, the container related configurations are under the node `di` and the
  service definitions are under `di.service` in the `$config` instance.



Features
---



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
