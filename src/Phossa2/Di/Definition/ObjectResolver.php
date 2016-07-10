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

namespace Phossa2\Di\Definition;

use Phossa2\Di\Traits\ContainerAwareTrait;
use Phossa2\Di\Interfaces\ContainerInterface;
use Phossa2\Config\Interfaces\ConfigInterface;
use Phossa2\Shared\Reference\DelegatorAwareTrait;
use Phossa2\Di\Interfaces\ContainerAwareInterface;
use Phossa2\Shared\Reference\DelegatorAwareInterface;

/**
 * Resolving object reference '#service_id' from DI container
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
class ObjectResolver implements ConfigInterface, ContainerAwareInterface, DelegatorAwareInterface
{
    use ContainerAwareTrait, DelegatorAwareTrait;

    /**
     * @param  ContainerInterface $container
     * @access public
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->setContainer($container);
    }

    /**
     * Get '#service_id' from the container
     *
     * {@inheritDoc}
     */
    public function get(/*# string */ $key, $default = null)
    {
        return $this->getContainer()->get(substr($key, 1));
    }

    /**
     * Has '#service_id' in the container ?
     *
     * {@inheritDoc}
     */
    public function has(/*# string */ $key)/*# : bool */
    {
        if (is_string($key) && '#' === substr($key, 0, 1)) {
            return $this->getContainer()->has(substr($key, 1));
        }
        return false;
    }
}
