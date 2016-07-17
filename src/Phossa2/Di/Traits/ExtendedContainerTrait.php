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

use Phossa2\Di\Interfaces\ScopeInterface;
use Phossa2\Di\Interfaces\FactoryInterface;
use Phossa2\Di\Interfaces\ResolverInterface;
use Phossa2\Di\Interfaces\ExtendedContainerInterface;

/**
 * ExtendedContainerTrait
 *
 * Implementation of ExtendedContainerInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ExtendedContainerInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait ExtendedContainerTrait
{
    // ExtendedContainerInterface related

    /**
     * {@inheritDoc}
     */
    public function one(/*# string */ $id, array $arguments = [])
    {
        return $this->get(
            $this->scopedId($id, ScopeInterface::SCOPE_SINGLE), $arguments
        );
    }

    /**
     * {@inheritDoc}
     */
    public function run($callable, array $arguments = [])
    {
        $this->resolve($callable);
        $this->resolve($arguments);

        return $this->getFactory()->executeCallable($callable, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function param(/*# string */ $name, $value)
    {
        $this->getResolver()->set((string) $name, $value);
        return $this;
    }

    // AutoWiringInterface related

    /**
     * {@inheritDoc}
     */
    public function auto(/*# bool */ $flag = true)
    {
        $this->getResolver()->auto($flag);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuto()/*# : bool */
    {
        return $this->getResolver()->isAuto();
    }

    // ReferenceResolveInterface related

    /**
     * {@inheritDoc}
     */
    public function resolve(&$toResolve)
    {
        $this->getResolver()->resolve($toResolve);
        return $this;
    }

    /**
     * From ContainerInterface
     *
     * @param string $id Identifier of the entry to look for.
     * @return mixed Entry.
     */
    abstract public function get($id);

    /**
     * From ContainerHelperTrait
     *
     * @return ResolverInterface
     */
    abstract protected function getResolver()/*# : ResolverInterface */;

    /**
     * From ContainerHelperTrait
     *
     * @return FactoryInterface
     */
    abstract protected function getFactory()/*# : FactoryInterface */;

    /**
     * From ScopeTrait
     *
     * @param  string $id
     * @param  string $scope
     * @return string
     */
    abstract protected function scopedId(
        /*# string */ $id,
        /*# string */ $scope
    )/*# : string */;
}
