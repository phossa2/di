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

use Phossa2\Di\Container;
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
    /**
     * @var    Container
     * @access protected
     */
    protected $master;

    /**
     * Get service definition (and fix it)
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
        $def = $this->master->getResolver()->getService($rawId);

        // fix class
        if (!is_array($def) || !isset($def['class'])) {
            $def = ['class' => $def];
        }

        // add resolved arguments
        if (!empty($args)) {
            $this->master->resolve($args);
            $def['args'] = $args;
        }

        return (array) $def;
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
     * Match provided arguments with a method/function's reflection parameters
     *
     * @param  \ReflectionParameter[] $reflectionParameters
     * @param  array $providedArguments
     * @return array the resolved arguments
     * @throws LogicException
     * @access protected
     */
    protected function matchArguments(
        array $reflectionParameters,
        array $providedArguments
    )/*# : array */ {
        // result
        $resolvedArguments = [];
        foreach ($reflectionParameters as $i => $param) {
            $class = $param->getClass();

            if ($this->isTypeMatched($class, $providedArguments)) {
                $resolvedArguments[$i] = array_shift($providedArguments);

            } elseif ($this->isRequiredClass($param, $providedArguments)) {
                $resolvedArguments[$i] = $this->getObjectByClass($class->getName());
            }
        }
        return array_merge($resolvedArguments, $providedArguments);
    }

    /**
     * Try best to guess parameter and argument are the same type
     *
     * @param  null|\ReflectionClass $class
     * @param  array $arguments
     * @return bool
     * @access protected
     */
    protected function isTypeMatched($class, array $arguments)/*# : bool */
    {
        if (empty($arguments)) {
            return false;
        } elseif (null !== $class) {
            return is_a($arguments[0], $class->getName());
        } else {
            return true;
        }
    }

    /**
     * Is $param required and is a class/interface
     *
     * @param  \ReflectionParameter $param
     * @param  array $arguments
     * @return bool
     * @throws LogicException if mismatched arguments
     * @access protected
     */
    protected function isRequiredClass(
        \ReflectionParameter $param,
        array $arguments
    )/*# : bool */ {
        $optional = $param->isOptional();
        if ($param->getClass()) {
            return !$optional || !empty($arguments);
        } else {
            return false;
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
        if ($this->master->getResolver()->hasMapping($classname)) {
            $classname = $this->master->getResolver()->getMapping($classname);
            if (is_object($classname)) {
                return $classname;
            }
        }
        return $this->master->getResolver()->get($classname);
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

        // object with __invoke() defined
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
    protected function mergeMethods(array $nodeData)/*# : array */
    {
        // no merge
        if (empty($nodeData) || isset($nodeData[0])) {
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
     * Execute common methods defined in 'di.common' for all objects
     *
     * Methods are in the form of
     *
     *   [ testCallable($obj, $container), runCallable($obj, $container)]
     *
     * @param  object $object
     * @return $this
     * @access protected
     */
    protected function executeCommonBatch($object)
    {
        $methods = [];

        // get from 'di.common'
        if ($this->master->getResolver()->hasInSection('', 'common')) {
            $methods = $this->mergeMethods(
                $this->master->getResolver()->getInSection('', 'common')
            );
        }

        foreach ($methods as $method) {
            if (call_user_func($method[0], $object, $this->master)) {
                call_user_func($method[1], $object, $this->master);
            }
        }
        return $this;
    }
}
