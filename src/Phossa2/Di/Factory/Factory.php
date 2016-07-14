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

use Phossa2\Di\Container;
use Phossa2\Di\Traits\FactoryTrait;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Di\Interfaces\FactoryInterface;

/**
 * Factory
 *
 * Wrapper of factorying methods for container
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     FactoryInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Factory extends ObjectAbstract implements FactoryInterface
{
    use FactoryTrait;

    /**
     * @param  Container $container
     * @access public
     */
    public function __construct(Container $container)
    {
        $this->master = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance(/*# string */ $rawId, array $arguments)
    {
        // get resolved definition
        $def = $this->getDefinition($rawId, $arguments);

        // arguments
        $args = isset($def['args']) ? $def['args'] : [];

        if (is_string($def['class'])) {
            $obj = $this->constructObject($def['class'], $args);

        } else {
            $obj = $this->executeCallable($def['class'], $args);
        }

        // execute after-creation methods
        $this->afterCreation($obj, $def);

        return $obj;
    }

    /**
     * {@inheritDoc}
     */
    public function executeCallable($callable, array $arguments = [])
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
     * {@inheritDoc}
     */
    public function executeMethodBatch(array $methods, $object = null)
    {
        foreach ($this->mergeMethods($methods) as $method) {
            $this->executeMethod($method, $object);
        }
    }

    /**
     * if $object provided, build callable like [$object, $method] and execute it.
     *
     * method:
     *
     * - callable
     *
     * - array ['function', [ arguments...]]
     *
     * - array [callable, [ arguments ...]]
     *
     * - array ['method', [ arguments ...]]
     *   will be converted to [[$object, 'method'], [ ... ]]
     *
     * @param  array|callable method
     * @param  object|null $object to construct callable
     * @throws LogicException if something goes wrong
     * @access protected
     */
    protected function executeMethod($method, $object = null)
    {
        // is callable
        if (is_callable($method)) {
            return $this->executeCallable($method);

        // is [ method, arguments ]
        } elseif (isset($method[0])) {
            return $this->executeCallable(
                $this->getObjectMethod($object, $method[0]), // callable
                isset($method[1]) ? $method[1] : [] // arguments
            );
        }
    }

    /**
     * Things to do after an object created.
     *
     * @param  object $object
     * @param  array $definition service definition for $object
     * @access protected
     */
    protected function afterCreation($object, array $definition)
    {
        // execute methods of THIS object
        if (isset($definition['methods'])) {
            $this->executeMethodBatch($definition['methods'], $object);
        }

        // execute common methods for all objects
        if (!isset($definition['skip']) || !$definition['skip']) {
            $this->executeCommonBatch($object);
        }
    }

    /**
     * Execute common methods defined in 'di.common' for all objects
     *
     * Methods are in the form of
     *
     *   [ interfaceOrClassname, [methodOrCallable, ArgumentsArray]],
     *   [ testCallable($obj, $container), [methodOrCallable, ArgumentsArray],
     *   ...
     *
     * @param  object $object
     * @return $this
     * @access protected
     */
    protected function executeCommonBatch($object)
    {
        foreach ($this->getCommonMethods() as $method) {
            $tester = $method[0];
            $runner = $method[1];
            if (call_user_func_array($tester, [$object, $this->master])) {
                $this->executeMethod($runner, $object);
            }
        }
        return $this;
    }

    /**
     * Get common methods
     *
     * @return array
     * @access protected
     */
    protected function getCommonMethods()/*# : array */
    {
        // get di.common node
        $methods = $this->mergeMethods(
            $this->master->getResolver()->getInSection('', 'common')
        );

        // fix tester
        foreach ($methods as $i => $pair) {
            if (is_string($pair[0])) {
                $tester = $pair[0];
                $methods[$i][0] = function($obj) use ($tester) {
                    return is_a($obj, $tester);
                };
            }
        }

        return $methods;
    }
}
