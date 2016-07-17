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

use Phossa2\Di\Interfaces\FactoryInterface;

/**
 * ContainerHelperTrait
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait ContainerHelperTrait
{
    use ResolverAwareTrait;

    /**
     * @var    FactoryInterface
     * @access protected
     */
    protected $factory;

    /**
     * Inject the Factory
     *
     * @param  FactoryInterface $factory
     * @return $this
     * @access protected
     */
    protected function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Get the Factory
     *
     * @return FactoryInterface
     * @access protected
     */
    protected function getFactory()/*# : FactoryInterface */
    {
        return $this->factory;
    }
}
