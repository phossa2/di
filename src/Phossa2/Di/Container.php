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
use Phossa2\Di\Scope\ScopeInterface;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Di\Exception\LogicException;
use Phossa2\Di\Exception\NotFoundException;
use Phossa2\Di\Definition\ResolverAwareTrait;
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
    use ResolverAwareTrait, FactoryTrait;

    /**
     * services pool
     *
     * @var    object[]
     * @access protected
     */
    protected $pool = [];

    /**
     * Constructor
     *
     * Inject a config instance which will provide configurations for all
     * the parameters, references and container related service and mapping
     * configurations
     *
     * @param  Config $config
     * @param  string $nodeName starting node in $config for the container
     * @access public
     */
    public function __construct(
        Config $config = null,
        /*# string */ $nodeName = 'di'
    ) {
        // setup the resolver
        $this->setResolver(
            new Resolver($this, $config ?: (new Config()), $nodeName)
        );

        // execute init methods defined in 'di.init' node
        $this->initContainer($nodeName);
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
            $id,
            func_num_args() > 1 ? func_get_arg(1) : []
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

        // not defined
        if (!$this->has($id)) {
            $this->getResolver()->setService($rawId, ['class' => $object]);
        }

        // non empty scope
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
            $this->scopedId($id, ScopeInterface::SCOPE_SINGLE),
            $arguments
        );
    }

    /**
     * {@inheritDoc}
     */
    public function run($callable, array $arguments = [])
    {
        return $this->factoryCallable($callable, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function batch(array $methods)
    {
        foreach ($methods as $mthd) {
            $this->run(
                $mthd['func'],
                isset($mthd['args']) ? $mthd['args'] : []
            );
        }
    }

    /**
     * Execute methods in the 'di.init' node
     *
     * @param  string $nodeName starting node for the container
     * @return $this
     * @access protected
     */
    protected function initContainer(/*# string */ $nodeName)
    {
        // node is 'di.init'
        $initNode = $nodeName . '.init';

        // has node defined
        if ($this->getResolver()->has($initNode)) {
            $init = $this->getResolver()->get($initNode);
            foreach ($init as $section => $methods) {
                $this->batch($methods);
            }
        }
    }

    /**
     * Get the instance either from the pool or create it
     *
     * @param  string $id
     * @param  array $args
     * @return object
     * @throws LogicException if instantiation goes wrong
     * @access protected
     */
    protected function getInstance(/*# string */ $id, array $args)
    {
        // get scoped id
        list($rawId, $scopedId, $scope) = $this->fullScopeInfo($id);

        // try the pool
        if (empty($args) && isset($this->pool[$scopedId])) {
            return $this->pool[$scopedId];
        }

        // create the instance
        $obj = $this->factoryInstance($rawId, $args);

        // cache it in the pool
        if (empty($args) && ScopeInterface::SCOPE_SHARED !== $scope) {
            $this->pool[$scopedId] = $obj;
        }

        return $obj;
    }
}
