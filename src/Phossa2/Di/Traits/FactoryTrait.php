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
use Phossa2\Di\Exception\NotFoundException;

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
        if (!is_array($def)) {
            $def = ['class' => $def];
        }

        // fix arguments
        if (!empty($args) || !isset($def['args'])) {
            // resolve external arguments
            $this->resolve($args);

            $def['args'] = $args;
        }

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
    protected function executeCallable($callable, array $arguments = [])
    {
        // not callable
        if (!is_callable($callable)) {
            return $callable;
        }

        if (!empty($arguments)) {
            $args = $this->matchCallableArguments($callable, $arguments);
            return call_user_func_array($callable, $args);
        } else {
            return call_user_func($callable);
        }
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

    /**
     * Matching callable arguments
     *
     * @param  callable $callable
     * @param  array $arguments
     * @return array the matched arguments
     * @throws LogicException if something goes wrong
     * @access protected
     */
    protected function matchCallableArguments(
        callable $callable,
        array $arguments
    )/*# : array */ {
        // array type
        if (is_array($callable)) {
            $reflector = new \ReflectionClass($callable[0]);
            $method = $reflector->getMethod($callable[1]);

        } elseif ($this->isInvocable($callable)) {
            $reflector = new \ReflectionClass($callable);
            $method = $reflector->getMethod('__invoke');

        // simple function
        } else {
            $method = new \ReflectionFunction($callable);
        }

        return $this->matchArguments(
            $method->getParameters(),
            $arguments
        );
    }

    /**
     * Match provided arguments with a method/function's reflection parameters
     *
     * @param  \ReflectionParameter[] $reflectionParameters
     * @param  array $providedArguments
     * @return array the resolved arguments
     * @throws LogicException
     * @throws NotFoundException
     * @access protected
     */
    protected function matchArguments(
        array $reflectionParameters,
        array $providedArguments
    )/*# : array */ {
        // result
        $resolvedArguments = [];

        // go thru each predefined parameter
        foreach ($reflectionParameters as $i => $param) {
            // arg to match with
            $argument = isset($providedArguments[0]) ? $providedArguments[0] : null;

            // $param is an interface or class ?
            $class = $param->getClass();

            if ($this->isTypeMatched($param, $argument, $class)) {
                // type matched
                $resolvedArguments[$i] = array_shift($providedArguments);

            } elseif (null !== $class) {
                // not matched, but $param is an interface or class
                $resolvedArguments[$i] = $this->getObjectByClass($class->getName());

            } elseif ($param->isOptional()) {
                // $param is optional, $arg is null
                break;
            } else {
                throw new LogicException(
                    Message::get(Message::DI_PARAMETER_NOTFOUND, $param->getName()),
                    Message::DI_PARAMETER_NOTFOUND
                );
            }
        }

        // append remained arguments if any
        if (!empty($providedArguments)) {
            $resolvedArguments = array_merge($resolvedArguments, $providedArguments);
        }

        return $resolvedArguments;
    }

    /**
     * Is $parameter same type as the $argument ?
     *
     * @param  \ReflectionParameter $parameter
     * @param  mixed $argument
     * @param  null|string $class
     * @return bool
     * @throws LogicException if type missmatch
     * @access protected
     */
    protected function isTypeMatched(
        \ReflectionParameter $parameters,
        $argument,
        $class
    )/*# : bool */ {
        if (null === $argument) {
            return false;
        } elseif (null !== $class) {
            return is_a($argument, $parameters->getClass()->getName());
        } else {
            return true;
        }
    }

    /**
     * Get an object base on provided classname or interface name
     *
     * @param  string $classname class or interface name
     * @return object
     * @throws \Exception if something goes wrong
     * @access protected
     */
    protected function getObjectByClass(/*# string */ $classname)
    {
        // mapping exists
        if ($this->getResolver()->hasMapping($classname)) {
            $classname = $this->getResolver()->getMapping($classname);
            if (is_object($classname)) {
                return $classname;
            }
        }
        return $this->get($classname);
    }

    /**
     * Is $var an object with '__invoke()' defined
     *
     * @param  mixed $var
     * @return bool
     * @access protected
     */
    protected function isInvocable($var)/*# : bool */
    {
        return is_object($var) && method_exists($var, '__invoke');
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
        // execute methods from this object
        if (isset($definition['methods'])) {
            foreach ($definition['methods'] as $method) {
                $this->executeMethod($method, $object);
            }
        }

        // execute common methods in 'di.common' for all created objects
        if ($this->getResolver()->has('', 'common')) {
            $methods = $this->mergeNodeInfo($this->getResolver()->get('', 'common'));
            $this->executeTester($object, $methods);
        }
    }

    /**
     * Execute [tester, todo] pairs, both use $object as argument
     *
     * signatures
     *
     * - tester: function($object) { return $object instance of XXXX; }
     * - todoer: function($object, $container) { }
     *
     * @param  object $object
     * @param  array $methods
     * @access protected
     */
    protected function executeTester($object, array $methods)
    {
        foreach ($methods as $method) {
            // tester: $method[0]
            if ($method[0]($object)) {
                // todoer: $method[1]
                $method[1]($object, $this);
            }
        }
    }

    /**
     * Merge data in the node, normally merge methods
     *
     * @param  array $nodeData
     * @return array
     * @access protected
     */
    protected function mergeNodeInfo(array $nodeData)/*# : array */
    {
        // no merge
        if (isset($nodeData[0])) {
            return $nodeData;
        }

        // in sections
        $result = [];
        foreach ($nodeData as $data) {
            $result = array_merge($result, $data);
        }
        return $result;
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
