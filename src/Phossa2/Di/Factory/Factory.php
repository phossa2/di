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

use Phossa2\Di\Message\Message;
use Phossa2\Di\Resolver\ObjectResolver;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Di\Exception\LogicException;
use Phossa2\Di\Traits\ResolverAwareTrait;
use Phossa2\Di\Interfaces\FactoryInterface;
use Phossa2\Di\Interfaces\ResolverInterface;

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
    use ResolverAwareTrait, FactoryHelperTrait;

    /**
     * @param  ResolverInterface
     * @access public
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->setResolver($resolver);
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance(/*# string */ $rawId, array $arguments)
    {
        // get service definition
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
            $methods = $this->getCommonMethods();
            $this->executeMethodBatch($methods, $object);
        }
    }

    /**
     * Get common methods
     *
     * @return array
     * @access protected
     */
    protected function getCommonMethods()/*# : array */
    {
        // di.common node
        $commNode = $this->getResolver()->getSectionId('', 'common');

        return $this->mergeMethods(
            $this->getResolver()->get($commNode)
        );
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
     * @param  array|null $nodeData
     * @return array
     * @access protected
     */
    protected function mergeMethods($nodeData)/*# : array */
    {
        // no merge
        if (empty($nodeData) || isset($nodeData[0])) {
            return (array) $nodeData;
        }

        // in sections
        $result = [];
        foreach ($nodeData as $data) {
            $result = array_merge($result, $data);
        }
        return $result;
    }

    /**
     * Returns [$object, $method] if it is a callable, otherwise returns $method
     *
     * @param  mixed $object
     * @param  mixed $method
     * @return bool
     * @access protected
     */
    protected function getObjectMethod($object, $method)/*# : bool */
    {
        if (is_string($method) && method_exists($object, $method)) {
            return [$object, $method];
        } elseif (is_callable($method)) {
            return $method;
        } else {
            throw new LogicException(
                Message::get(Message::DI_CALLABLE_BAD, $method),
                Message::DI_CALLABLE_BAD
            );
        }
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

        // add arguments
        if (!empty($args)) {
            $def['args'] = $args;
        }

        return (array) $def;
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
        if ($this->getResolver()->hasService($classname)) {
            $serviceId = ObjectResolver::getServiceId($classname);
            return $this->getResolver()->get($serviceId);
        }
        throw new LogicException(
            Message::get(Message::DI_CLASS_UNKNOWN, $classname),
            Message::DI_CLASS_UNKNOWN
            );
    }
}
