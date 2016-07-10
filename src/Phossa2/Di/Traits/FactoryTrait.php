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
    use UtilityTrait;

    /**
     * for loop detection
     *
     * @var    array
     * @access protected
     */
    protected $loop = [];

    /**
     * a counter
     *
     * @var    int
     * @access protected
     */
    protected $counter = 0;

    /**
     * Full scope info
     *
     * @param  string $id
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
        $this->loop[$serviceId] = ++$this->counter;

        // create the service instance
        $obj = $this->createFromId($rawId, $args);

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
    protected function executeCallable($callable, array $arguments = [])
    {
        // not callable
        if (!is_callable($callable)) {
            return $callable;
        }

        if (!empty($arguments)) {
            $params = $this->getCallableParameters($callable);
            $args = $this->matchArguments($params, $arguments);
            return call_user_func_array($callable, $args);
        } else {
            return call_user_func($callable);
        }
    }

    /**
     * Things to do after object created.
     *
     * @param  object $object
     * @param  array $definition service definition for $object
     * @access protected
     */
    protected function afterCreation($object, array $definition)
    {
        // execute methods of this object
        $this->executeObjectMethods($object, $definition);

        // execute common methods for all objects
        $this->executeCommonMethods($object);
    }

    /**
     * Execute objects's own methods defined in its 'node.methods'
     *
     * @param  object $object
     * @return $this
     * @access protected
     */
    protected function executeObjectMethods($object, array $definition)
    {
        if (isset($definition['methods'])) {
            foreach ($definition['methods'] as $method) {
                $this->executeMethod($method, $object);
            }
        }
        return $this;
    }

    /**
     * Execute common methods defined in 'di.common' for objects
     *
     * @param  object $object
     * @return $this
     * @access protected
     */
    protected function executeCommonMethods($object)
    {
        if ($this->getResolver()->has('', 'common')) {
            $methods = $this->mergeNodeInfo($this->getResolver()->get('', 'common'));
            $this->executeTester($object, $methods);
        }
        return $this;
    }

    /**
     * Rebuild callable base methodName and object
     *
     * method:
     * - ['function', [ arguments...]]
     *
     * - [ callable, [ arguments ...]]
     *
     * - ['method', [ arguments ...]]
     *   convert to [[$object, 'method'], [ ... ]]
     *
     * @param  mixed method
     * @param  object|null $object to construct callable
     * @throws LogicException if something goes wrong
     * @access protected
     */
    protected function executeMethod($method, $object = null)
    {
        $callable  = $method[0];
        $arguments = isset($method[1]) ? $method[1] : [];

        // rebuild callable from $object
        if (null !== $object) {
            $callable = [$object, $callable];
        }

        $this->executeCallable($callable, $arguments);
    }
}
