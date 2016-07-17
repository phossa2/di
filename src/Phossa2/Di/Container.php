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
use Phossa2\Di\Traits\ArrayAccessTrait;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Config\Traits\WritableTrait;
use Interop\Container\ContainerInterface;
use Phossa2\Di\Interfaces\ScopeInterface;
use Phossa2\Di\Exception\RuntimeException;
use Phossa2\Di\Exception\NotFoundException;
use Phossa2\Di\Traits\ExtendedContainerTrait;
use Phossa2\Config\Interfaces\ConfigInterface;
use Phossa2\Config\Interfaces\WritableInterface;
use Phossa2\Shared\Delegator\DelegatorAwareTrait;
use Phossa2\Di\Interfaces\ExtendedContainerInterface;
use Phossa2\Shared\Delegator\DelegatorAwareInterface;

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
 *   $delegator->addContainer($container);
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
 * @see     ExtendedContainerInterface
 * @see     DelegatorAwareInterface
 * @see     WritableInterface
 * @see     \ArrayAccess
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Container extends ObjectAbstract implements ContainerInterface, ScopeInterface, WritableInterface, \ArrayAccess, DelegatorAwareInterface, ExtendedContainerInterface
{
    use WritableTrait,
        ArrayAccessTrait,
        DelegatorAwareTrait,
        ExtendedContainerTrait;

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
     * @param  ConfigInterface $config inject the config instance
     * @param  string $baseNode container's starting node in $config
     * @access public
     */
    public function __construct(
        ConfigInterface $config = null,
        /*# string */ $baseNode = 'di'
    ) {
        // set resolver
        $this->setResolver(
            new Resolver($this, $config ?: new Config(), $baseNode)
        );

        // set factory
        $this->setFactory(new Factory($this->getResolver()));

        // run methods in 'di.init'
        $this->initContainer();
    }

    // ContainerInterface related

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
        if ($this->has($id)) {
            $args = func_num_args() > 1 ? func_get_arg(1) : [];
            $this->resolve($args);
            return $this->getInstance($id, $args);
        } else {
            throw new NotFoundException(
                Message::get(Message::DI_SERVICE_NOTFOUND, $id),
                Message::DI_SERVICE_NOTFOUND
            );
        }
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

    // ExtendedContainerInterface

    /**
     * {@inheritDoc}
     */
    public function one(/*# string */ $id, array $arguments = [])
    {
        return $this->get(
            $this->scopedId($id, ScopeInterface::SCOPE_SINGLE), $arguments
        );
    }

    // WritableInterface related

    /**
     * {@inheritDoc}
     */
    public function set(/*# string */ $id, $value)
    {
        if ($this->isWritable()) {
            list($rawId, $scope) = $this->splitId($id);

            $this->getResolver()->setService(
                $rawId,
                '' === $scope ? $value : $this->scopedData($value, $scope)
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
        return $this->getResolver()->setWritable((bool) $writable);
    }

    /**
     * execute init methods defined in 'di.init' node
     *
     * @return $this
     * @throws RuntimeException if anything goes wrong
     * @access protected
     */
    protected function initContainer()
    {
        $initNode = $this->getResolver()->getSectionId('', 'init');

        if ($this->getResolver()->has($initNode)) {
            $this->getFactory()->executeMethodBatch(
                $this->getResolver()->get($initNode)
            );
        }
        return $this;
    }
}
