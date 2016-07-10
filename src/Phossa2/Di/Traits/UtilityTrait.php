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
 * UtilityTrait
 *
 * Collection of utility methods
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait UtilityTrait
{
    use ScopeTrait;

    /**
     * Is $var an object with '__invoke()' defined ?
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
     * Merge different sections of a node
     *
     * convert
     *   `['section1' => [[1], [2]], 'section2' => [[3], [4]]]`
     *
     * to
     *   `[[1], [2], [3], [4]]`
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
     * Prepend '#' to rawId, representing a service object id
     *
     * @param  string $rawId
     * @return string
     * @access protected
     */
    protected function getServiceId(/*# string */ $rawId)/*# : string */
    {
        return '#' . $rawId;
    }

    /**
     * Execute [tester, todo] pairs, both use $object and $container as arguments
     *
     * signatures
     * - tester: function($object, $container) { return $object instance of XXXX; }
     * - todoer: function($object, $container) { }
     *
     * @param  object $object
     * @param  array $methods
     * @access protected
     */
    protected function executeTester($object, array $methods)
    {
        foreach ($methods as $method) {
            if ($method[0]($object, $this)) {
                $method[1]($object, $this);
            }
        }
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
        \ReflectionParameter $parameter,
        $argument,
        $class
    )/*# : bool */ {
        if (null === $argument) {
            return false;
        } elseif (null !== $class) {
            return is_a($argument, $parameter->getClass()->getName());
        } else {
            return true;
        }
    }

    /**
     * Get callable parameters
     *
     * @param  callable $callable
     * @return \ReflectionParameter[]
     * @throws LogicException if something goes wrong
     * @access protected
     */
    protected function getCallableParameters(callable $callable)/*# : array */
    {
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

        return $method->getParameters();
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

            // check the class
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
     * Get service definition and fix it
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
            $this->resolve($args);
            $def['args'] = $args;
        }

        return $def;
    }

    /**
     * @param string $id Identifier of the entry to look for.
     * @return mixed Entry.
     */
    abstract public function get($id);
}
