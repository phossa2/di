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
use Phossa2\Di\Scope\ScopeTrait;
use Phossa2\Di\Exception\LogicException;

/**
 * FactoryTrait
 *
 * Create service instance here
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait FactoryTrait
{
    use ScopeTrait;

    /**
     * for loop detection
     *
     * @var    array
     * @access protected
     */
    protected $loop = [];

    /**
     * Full scope info
     *
     * @param  sting $id
     * @return array
     * @access protected
     */
    protected function fullScopeInfo(/*# string */ $id)/*# : array */
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
     * @param  string $rawId
     * @param  array $args arguments for the constructor if any
     * @return object
     * @throws LogicException if instantiation goes wrong or loop detected
     * @access protected
     */
    protected function createInstance(/*# string */ $rawId, array $args)
    {
        static $counter = 0;

        // conver 'service_id' to '#service_id'
        $serviceId = $this->getServiceId($rawId);

        // loop detected
        if (isset($this->loop[$serviceId])) {
            throw new LogicException(
                Message::get(Message::DI_LOOP_DETECTED, $rawId),
                Message::DI_LOOP_DETECTED
            );
        }

        // set loop marker
        $this->loop[$serviceId] = ++$counter;

        // create the service object
        try {
            $obj = $this->createFromId($rawId, $args);
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage(), $e->getCode());
        }

        // remove current marker
        unset($this->loop[$serviceId]);

        return $obj;
    }

    /**
     * Create object base on the raw id
     *
     * @param  string $rawId
     * @param  array $arguments
     * @return object
     * @throws LogicException if instantiation goes wrong
     * @access protected
     */
    protected function createFromId(/*# string */ $rawId, array $arguments)
    {
        // get definition
        $def = $this->getDefinition($rawId, $arguments);

        if (is_string($def['class'])) {
            // classname
            $obj = $this->constructObject($def['class'], $def['args']);

        } else {
            // object or callable etc.
            $obj = $this->executeCallable($def['class'], $def['args']);
        }

        // after creation
        $this->afterCreation($obj, $def);

        return $obj;
    }

    /**
     * Get service definition
     *
     * @param  string $rawId
     * @param  array $args
     * @return array
     * @access protected
     */
    protected function getDefinition(
        /*# string */ $rawId,
        array $args
    )/*# : array */ {
        // get the definition
        $def = $this->getResolver()->getService($rawId);

        // fix class
        if (!is_array($def) || !isset($def['class'])) {
            $def = ['class' => $def];
        }

        // fix arguments
        if (!empty($args) || !isset($def['args'])) {
            $def['args'] = $args;
        }

        // resolve class
        $this->resolve($def['class']);

        return $def;
    }

    /**
     * Instantiate service object from classname
     *
     * @param  string $class
     * @param  array $args
     * @return object
     * @throws LogicException if something goes wrong
     * @access protected
     */
    protected function constructObject(/*# string */ $class, array $args)
    {
        $reflector = new \ReflectionClass($class);
        $constructor = $reflector->getConstructor();

        // not constructor defined
        if (is_null($constructor)) {
            $obj = $reflector->newInstanceWithoutConstructor();

        // normal class with constructor
        } else {
            $args = $this->matchArguments(
                $constructor->getParameters(),
                $args
            );
            $obj = $reflector->newInstanceArgs($args);
        }

        return $obj;
    }

    /**
     * Execute a (pseudo) callable with arguments
     *
     * @param  callable|array|object $callable callable or pseudo callable
     * @param  array $arguments
     * @return mixed
     * @throws LogicException if something goes wrong
     * @access protected
     */
    protected function executeCallable($callable, array $arguments)
    {
        // resolving any references
        $this->resolve($callable);

        // not callable
        if (!is_callable($callable)) {
            return $callable;
        }

        // resolve argument
        $this->resolve($arguments);

        return call_user_func_array($callable, $arguments);
    }

    /**
     * Append '#' to rawId, representing a service object id
     *
     * @param  string $rawId
     * @return string
     * @access protected
     */
    protected function getServiceId(/*# string */ $rawId)/*# : string */
    {
        return '#' . $rawId;
    }
}
