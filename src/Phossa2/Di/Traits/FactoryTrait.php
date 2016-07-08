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
use Phossa2\Di\Definition\ResolverAwareTrait;

/**
 * FactoryTrait
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait FactoryTrait
{
    use ScopeTrait, ResolverAwareTrait;

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

        // if $scope is a '#service_id'
        if (isset($this->loop[$scope])) {
            $scope .= '_' . $this->loop[$scope];
        }

        return [$rawId, $this->scopedId($rawId, $scope), $scope];
    }

    /**
     * Create the instance with loop detection built-in
     *
     * @param  string $rawId
     * @param  array $arguments
     * @return object
     * @throws LogicException if anything goes wrong
     * @access protected
     */
    protected function createInstance(
        /*# string */ $rawId,
        array $arguments = []
    ) {
        static $counter = 0;

        // service id
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
        $obj = $this->createFromId($rawId, $arguments);

        // remove loop marker
        unset($this->loop[$serviceId]);

        return $obj;
    }

    /**
     * Create object base on the id
     *
     * @param  string $rawId
     * @param  array $args if any new args provided
     * @return object
     * @access protected
     */
    protected function createFromId(
        /*# string */ $rawId,
        array $args
    ) {
        // definition base on id
        $def = $this->getResolver()->getService($rawId);

        // fix definition
        if (!is_array($def) || !isset($def['class'])) {
           $def = ['class' => $def];
        }

        // overwrite arguments if provided
        if (!empty($args)) {
            $def['args'] = $args;
        }

        return $this->createFromDefinition($def);
    }

    /**
     * Construct object base on the definition
     *
     * @param  array $definition
     * @return object
     * @access protected
     */
    protected function createFromDefinition(array $definition)
    {
        // get class
        $class = $definition['class'];
        $this->resolve($class);

        // get args
        $args  = isset($definition['args']) ? $definition['args'] : [];

        // callable
        if (is_array($class) || is_callable($class)) {
            $obj = $this->executeCallable($class, $args);

        // object
        } elseif (is_object($class)) {
            $obj = $class;

        // classname
        } else {
            $obj = $this->constructObject($class, $args);
        }

        // after creation
        $this->afterCreation($obj, $definition);

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
    protected function constructObject(
        /*# string */ $class,
        array $args
    ) {
        $reflector = new \ReflectionClass($class);
        $constructor = $reflector->getConstructor();

        // not constructor defined
        if (is_null($constructor)) {
            return $reflector->newInstanceWithoutConstructor();

        // normal class with constructor
        } else {
            $args = $this->matchArguments(
                $constructor->getParameters(),
                $args
            );
            return $reflector->newInstanceArgs($args);
        }
    }

    /**
     * Execute a (pseudo) callable with arguments
     *
     * @param  callable|array $callable callable or pseudo callable
     * @param  array $arguments
     * @return mixed
     * @throws LogicException if something goes wrong
     * @access protected
     */
    protected function executeCallable($callable, array $arguments)
    {
        try {
            // resolving any references
            $this->resolve($callable);

            // argument matching
            if (!empty($arguments)) {
                $this->matchCallableArguments($callable, $arguments);
            }

            return call_user_func_array($callable, $arguments);

        } catch (\Exception $e) {
            throw new LogicException($e->getMessage(), $e->getCode());
        }
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
