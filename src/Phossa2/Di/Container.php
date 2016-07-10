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
use Phossa2\Di\Traits\FactoryTrait;
use Phossa2\Di\Definition\Resolver;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Di\Exception\LogicException;
use Phossa2\Di\Interfaces\ScopeInterface;
use Phossa2\Di\Exception\NotFoundException;
use Phossa2\Di\Definition\ResolverInterface;
use Phossa2\Di\Interfaces\ContainerInterface;
use Phossa2\Di\Definition\ResolverAwareInterface;
use Phossa2\Di\Interfaces\ExtendedContainerInterface;

/**
 * Container
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ObjectAbstract
 * @see     ContainerInterface
 * @see     DefinitionInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Container extends ObjectAbstract implements ContainerInterface, ResolverAwareInterface, ScopeInterface, ExtendedContainerInterface
{
    use FactoryTrait;

    /**
     * instances pool
     *
     * @var    object[]
     * @access protected
     */
    protected $pool = [];

    /**
     * Constructor
     *
     * Inject a config instance which will provide configs for all the
     * definitions, parameters, mappings. $nodeName is the starting node
     * in the $config for container. normally it is the 'di' node.
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
     * // init $config
     * $config = new Config(null, null, $configData);
     *
     * // init container
     * $container = new $config['di.class']($config);
     * ```
     *
     * @param  Config $config
     * @param  ResolverInterface $resolver if injected
     * @param  string $baseNode starting node
     * @access public
     */
    public function __construct(
        Config $config = null,
        ResolverInterface $resolver = null,
        /*# string */ $baseNode = 'di'
    ) {
        // setup the resolver
        if (null === $resolver) {
            $resolver = new Resolver($this, $config ?: (new Config()), $baseNode);
        }

        $this->setResolver($resolver->setBaseNode($baseNode));

        // reserve 'di.service.container', later can be used as '${#container}'
        $this->registerSelf();

        // execute init methods defined in 'di.init' node
        $this->executeNode($baseNode . '.init');
    }

    /**
     * - Accepting second param as constructor arguments
     * - Accpeting $id with scope like 'cache@myScope'
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

        // get the instance
        return $this->getInstance(
            $id, func_num_args() > 1 ? func_get_arg(1) : []
        );
    }

    /**
     * - Accpeting $id with scope like 'cache@myScope'
     *
     * {@inheritDoc}
     */
    public function has($id)
    {
        if (is_string($id)) {
            $rawId = $this->splitId($id)[0];
            return $this->getResolver()->hasService($rawId);
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function set(/*# string */ $id, $object)
    {
        list($rawId, $scope) = $this->splitId($id);
        $this->getResolver()->setService($rawId, $object);

        // if $scope found, put this instance in the pool
        if (!empty($scope) && is_object($object)) {
            $this->pool[$id] = $object;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function one(/*# string */ $id, array $arguments = [])
    {
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
        // resolve external stuff
        $this->resolve($callable);
        $this->resolve($arguments);

        return $this->executeCallable($callable, $arguments);
    }

    /**
     * Register 'container' in container to be used as '${#container}' later
     *
     * @return $this
     * @access protected
     */
    protected function registerSelf()
    {
        // set in definition
        $this->set(
            'container',
            ['class' => $this, 'scope' => self::SCOPE_SHARED]
        );

        // put in the pool to skip running common methods
        $this->pool[$this->scopedId('container', self::SCOPE_SHARED)] = $this;

        return $this;
    }

    /**
     * Execute methods defined in the node
     *
     * ```php
     * $node = [
     *    'section1' => [
     *        [callable1, arguments],
     *        ...
     *    ],
     *    'section2' => [
     *        ...
     *    ],
     *    ...
     * ];
     * ```
     *
     * @param  string $nodeName
     * @return $this
     * @access protected
     */
    protected function executeNode(/*# string */ $nodeName)
    {
        // Is node defined ?
        if (!$this->getResolver()->has($nodeName)) {
            return;
        }

        // get the node
        $nodeData = $this->getResolver()->get($nodeName);

        // merge all methods from the node
        $methods = $this->mergeNodeInfo($nodeData);

        // execute in batch
        foreach ($methods as $method) {
            $this->executeMethod($method);
        }
    }

    /**
     * Get the instance either from the pool or create it
     *
     * @param  string $id service id with or without the scope
     * @param  array $args arguments for the constructor
     * @return object
     * @throws LogicException if instantiation goes wrong
     * @access protected
     */
    protected function getInstance(/*# string */ $id, array $args)
    {
        // get id & scope info
        list($rawId, $scopedId, $scope) = $this->fullScopeInfo($id);

        // get a new instance if args or in single scope
        if (!empty($args) || ScopeInterface::SCOPE_SINGLE === $scope) {
            return $this->createInstance($rawId, $args);
        }

        // if not in the pool, create one
        if (!isset($this->pool[$scopedId])) {
            $this->pool[$scopedId] = $this->createInstance($rawId, []);
        }

        return $this->pool[$scopedId];
    }
}
