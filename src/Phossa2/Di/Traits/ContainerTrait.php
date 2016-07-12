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

namespace Phossa2\Di\Traits;

use Phossa2\Di\Message\Message;
use Phossa2\Di\Resolver\ObjectResolver;
use Phossa2\Di\Exception\LogicException;
use Phossa2\Di\Interfaces\ScopeInterface;

trait ContainerTrait
{
    use ScopeTrait, FactoryAwareTrait;

    /**
     * instances pool
     *
     * @var    object[]
     * @access protected
     */
    protected $pool = [];

    /**
     * for loop detection
     *
     * @var    array
     * @access protected
     */
    protected $loop = [];

    /**
     * @var    int
     * @access protected
     */
    protected $counter = 0;

    /**
     * execute init methods defined in 'di.init' node
     *
     * @return $this
     * @throws RuntimeException if anything goes wrong
     * @access protected
     */
    protected function initContainer()
    {
        if ($this->getResolver()->hasInSection('', 'init')) {
            $this->getFactory()->executeMethodBatch(
                $this->getResolver()->getInSection('', 'init')
            );
        }
        return $this;
    }

    /**
     * Get the instance either from the pool or create it
     *
     * @param  string $id service id with or without the scope
     * @param  array $args arguments for the constructor
     * @return object
     * @throws LogicException if instantiation goes wrong
     * @throws RuntimeException if method execution goes wrong
     * @access protected
     */
    protected function getInstance(/*# string */ $id, array $args)
    {
        // get id & scope info
        list($rawId, $scopedId, $scope) = $this->realScopeInfo($id);

        // get a new instance if args not empty or in single scope
        if (!empty($args) || ScopeInterface::SCOPE_SINGLE === $scope) {
            return $this->createInstance($rawId, $args);
        }

        // if not in the pool, create a new instance
        if (!isset($this->pool[$scopedId])) {
            $this->pool[$scopedId] = $this->createInstance($rawId);
        }

        return $this->pool[$scopedId];
    }

    /**
     * Full scope info with consideration of ancestor instances
     *
     * @param  string $id
     * @return array
     * @access protected
     */
    protected function realScopeInfo(/*# string */ $id)/*# : array */
    {
        list($rawId, $scope) = $this->scopedInfo($id);

        // special treatment if $scope is a '#service_id'
        if (isset($this->loop[$scope])) {
            $scope .= '_' . $this->loop[$scope];
        }

        return [$rawId, $this->scopedId($rawId, $scope), $scope];
    }

    /**
     * Create the instance with loop detection
     *
     * Loop: an instance depends on itself in the creation chain.
     *
     * @param  string $rawId
     * @param  array $args arguments for the constructor if any
     * @return object
     * @throws LogicException if instantiation goes wrong or loop detected
     * @access protected
     */
    protected function createInstance(/*# string */ $rawId, array $args = [])
    {
        // conver 'service_id' to '#service_id'
        $serviceId = ObjectResolver::getServiceId($rawId);

        if (isset($this->loop[$serviceId])) {
            // found self in the chain
            throw new LogicException(
                Message::get(Message::DI_LOOP_DETECTED, $rawId),
                Message::DI_LOOP_DETECTED
            );
        } else {
            $this->loop[$serviceId] = ++$this->counter;
            $obj = $this->getFactory()->createInstance($rawId, $args);
            unset($this->loop[$serviceId]);
            return $obj;
        }
    }
}
