<?php
/**
 * Phossa Project
 *
 * PHP version 5.4
 *
 * @category  Library
 * @package   Phossa2\Di
 * @copyright Copyright (c) 2016 phossa.com
 * @license   http://mit-license.org/ MIT License
 * @link      http://www.phossa.com/
 */
/*# declare(strict_types=1); */

namespace Phossa2\Di;

use Phossa2\Config\Config;
use Phossa2\Di\Message\Message;
use Phossa2\Di\Factory\Factory;
use Phossa2\Di\Resolver\Resolver;
use Phossa2\Di\Traits\ContainerTrait;
use Phossa2\Di\Traits\ArrayAccessTrait;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Config\Traits\WritableTrait;
use Interop\Container\ContainerInterface;
use Phossa2\Di\Interfaces\ScopeInterface;
use Phossa2\Di\Exception\RuntimeException;
use Phossa2\Di\Exception\NotFoundException;
use Phossa2\Shared\Reference\DelegatorInterface;
use Phossa2\Config\Interfaces\WritableInterface;
use Phossa2\Di\Interfaces\FactoryAwareInterface;
use Phossa2\Di\Interfaces\ResolverAwareInterface;
use Phossa2\Shared\Reference\DelegatorAwareTrait;
use Phossa2\Di\Interfaces\ExtendedContainerInterface;
use Phossa2\Shared\Reference\DelegatorAwareInterface;

/**
 * Container
 *
 * A writable, array accessable, delegator-aware and extended instance container.
 *
 * - writable:
 *
 *   ```php
 *   $container->set('cache', $cache);
 *   ```
 *
 * - array accessable:
 *
 *   ```php
 *   // get
 *   $cache = $container['cache'];
 *
 *   // set/replace
 *   $container['cache'] = $anotherCache;
 *   ```
 *
 * - delegator-aware: lookup dependent instances in the delegator
 *
 *   ```php
 *   $delegator->addRegistry($container);
 *   ```
 *
 * - extended container
 *
 *   ```php
 *   // get new instance
 *   $newCache = $container->one('cache');
 *
 *   // run callables
 *   $container->run(['${#logger}', 'warning'], ['A warning message from ${user}']);
 *   ```
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ObjectAbstract
 * @see     ScopeInterface
 * @see     ContainerInterface
 * @see     ResolverAwareInterface
 * @see     FactoryAwareInterface
 * @see     ExtendedContainerInterface
 * @see     DelegatorAwareInterface
 * @see     WritableInterface
 * @see     \ArrayAccess
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Container extends ObjectAbstract implements ContainerInterface, ResolverAwareInterface, FactoryAwareInterface, ScopeInterface, ExtendedContainerInterface, DelegatorAwareInterface, \ArrayAccess, WritableInterface
{
    use ContainerTrait, ArrayAccessTrait, DelegatorAwareTrait, WritableTrait;

    /**
     * Inject a Phossa2\Config\Config
     *
     * ```php
     * $configData = [
     *     // container class
     *     'di.class' => 'Phossa2\\Di\\Container',
     *
     *     // container service definitions
     *     'di.service' => [
     *         // ...
     *     ],
     *
     *     // interface/classname mappings
     *     'di.mapping' => [
     *     ],
     *
     *     // init methods to run after container created
     *     'di.init' => [
     *         'default' => [],
     *         'mystuff' => [ ... ],
     *     ],
     * ];
     *
     * // instantiate $config
     * $config = new Config(null, null, $configData);
     *
     * // instantiate container
     * $container = new $config['di.class']($config);
     * ```
     *
     * @param  Config $config inject the config instance
     * @param  string $baseNode container's starting node in $config
     * @access public
     */
    public function __construct(
        Config $config = null,
        /*# string */ $baseNode = 'di'
    ) {
        $conf = $config ?: new Config();

        $this
            ->setResolver(new Resolver($this, $conf, $baseNode))
            ->setFactory(new Factory($this))
            ->registerObject('container', $this)
            ->registerObject('config', $conf)
            ->initContainer();
    }

    /**
     * Extensions to the Interop\Container\ContainerInterface
     *
     * - Accepting second param as object constructor arguments
     * - Accpeting $id with scope appended, e.g. 'cache@myScope'
     *
     * {@inheritDoc}
     */
    public function get($id)
    {
        // not found
        if (!$this->has($id)) {
            throw new NotFoundException(
                Message::get(Message::DI_SERVICE_NOTFOUND, $id),
                Message::DI_SERVICE_NOTFOUND
            );
        }

        // get the instance, constructor args if any
        return $this->getInstance(
            $id, func_num_args() > 1 ? func_get_arg(1) : []
        );
    }

    /**
     * Extensions to the Interop\Container\ContainerInterface
     *
     * - Accpeting $id with scope appended, e.g. 'cache@myScope'
     *
     * {@inheritDoc}
     */
    public function has($id)
    {
        if (is_string($id)) {
            return $this->getResolver()->hasService(
                $this->idWithoutScope($id)
            );
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function set(/*# string */ $id, $value)
    {
        if ($this->isWritable()) {
            // set in service definition
            $this->getResolver()->setService(
                $this->idWithoutScope($id),
                $value
            );
            return $this;
        } else {
            throw new RuntimeException(
                Message::get(Message::DI_CONTAINER_READONLY, $id),
                Message::DI_CONTAINER_READONLY
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function one(/*# string */ $id, array $arguments = [])
    {
        // set in single scope
        return $this->get(
            $this->scopedId($id, self::SCOPE_SINGLE),
            $arguments
        );
    }

    /**
     * {@inheritDoc}
     */
    public function run($callable, array $arguments = [])
    {
        // resolve any references in the callable(array) and arguments
        $this->resolve($callable);
        $this->resolve($arguments);

        return $this->getFactory()->executeCallable($callable, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function map(/*# string */ $from, $to)
    {
        $this->getResolver()->setMapping($from, $to);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function auto(/*# bool */ $on = true)
    {
        $this->getResolver()->autoWiring($on);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function param(/*# string */ $name, $value)
    {
        $this->getResolver()->set((string) $name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(&$toResolve)
    {
        $this->getResolver()->resolve($toResolve);
        return $this;
    }

    /**
     * Override `setDelegator()` from 'Phossa2\Shared\Reference\DelegatorAwareTrait'
     *
     * {@inheritDoc}
     */
    public function setDelegator(DelegatorInterface $delegator)
    {
        $this->delegator = $delegator;

        // this will make sure all dependencies will be looked up in the delegator
        $this->getResolver()->setObjectResolver();

        return $this;
    }

    /**
     * Override 'isWritable()' in 'Phossa2\Config\Traits\WritableTrait'
     *
     * Container's writability is depend on its resolver
     *
     * {@inheritDoc}
     */
    public function isWritable()/*# : bool */
    {
        return $this->getResolver()->isWritable();
    }

    /**
     * Override 'setWritable()' in 'Phossa2\Config\Traits\WritableTrait'
     *
     * Container's writability is depend on its resolver
     *
     * {@inheritDoc}
     */
    public function setWritable($writable)/*# : bool */
    {
        $this->getResolver()->setWritable((bool) $writable);
        return $this;
    }

    /**
     * Register object in 'di.service' with $name
     *
     * e.g. `$this->registerObject('container', $this)`
     *
     * - Later, $this can be referenced as '${#container}' anywhere
     *
     * - $object will skip execute common methods for created instances.
     *
     *   instead of just do
     *      `$container->set($name, $object)`
     *
     *   you may do
     *      $container->set($name, ['class' => $object, 'skip' => true]);
     *
     * @param  string $name name to register with
     * @param  object $object
     * @return $this
     * @access protected
     */
    protected function registerObject(/*# string */ $name, $object)
    {
        if (!$this->has($name) && $this->isWritable()) {
            $this->set($name, ['class' => $object, 'skip' => true]);
        }
        return $this;
    }
}
