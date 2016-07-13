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
        $merged = $this->mergeMethods($methods);

        foreach ($merged as $method) {
            $this->executeMethod($method, $object);
        }
    }

    /**
     * if $object provided, build callable like [$object, $method] and execute it.
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
        if (null !== $object &&
            is_string($callable) &&
            method_exists($object, $callable)
        ) {
            $callable = [$object, $callable];
        }

        $this->executeCallable($callable, $arguments);
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
        $methods = [];

        // get methods from 'di.common'
        if ($this->master->getResolver()->hasInSection('', 'common')) {
            $methods = $this->mergeMethods(
                $this->master->getResolver()->getInSection('', 'common')
            );
        }

        foreach ($methods as $method) {
            $tester = $method[0];
            if (is_string($tester) && is_a($object, $tester) ||
                call_user_func($tester, $object, $this->master)
            ) {
                $this->executeMethod($method[1], $object);
            }
        }
        return $this;
    }
}
