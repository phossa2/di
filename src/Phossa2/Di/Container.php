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
     * @param  Config $config
     * @param  string $baseNode starting node
     * @access public
     */
    public function __construct(
        Config $config = null,
        /*# string */ $baseNode = 'di'
    ) {
        // setup the resolver
        $this->setResolver(
            new Resolver($this, $config ?: (new Config()), $baseNode)
        );

        // reserve 'container', later can be referenced as '${#container}'
        $this->set('container', $this);

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
        if (!empty($scope)) {
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
        $node = $this->getResolver()->get($nodeName);

        // merge all methods from the node
        $batch = [];
        foreach ($node as $methods) {
            $batch = array_merge($batch, $methods);
        }

        // execute in batch
        foreach ($batch as $method) {
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
