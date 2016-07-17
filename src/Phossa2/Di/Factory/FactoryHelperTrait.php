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

namespace Phossa2\Di\Factory;

use Phossa2\Di\Exception\LogicException;

/**
 * FactoryHelperTrait
 *
 * Create service instance here
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait FactoryHelperTrait
{

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
            $args = $this->matchArguments($constructor->getParameters(), $args);
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
     * Is $var a non-closure object with '__invoke()' defined ?
     *
     * @param  mixed $var
     * @return bool
     * @access protected
     */
    protected function isInvocable($var)/*# : bool */
    {
        return is_object($var) &&
            !$var instanceof \Closure &&
            method_exists($var, '__invoke');
    }
}
