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

use Phossa2\Di\Interfaces\ContainerInterface;
use Phossa2\Config\Interfaces\ConfigInterface;

/**
 * Resolving object reference '#service_id' from DI container
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
class ObjectResolver implements ConfigInterface
{
    /**
     * @var    ContainerInterface
     * @access private
     */
    private $container;

    /**
     * @param  ContainerInterface $container
     * @access public
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * Get '#service_id' from the container
     *
     * {@inheritDoc}
     */
    public function get(/*# string */ $key, $default = null)
    {
        return $this->container->get(substr($key, 1));
    }

    /**
     * Has '#service_id' in the container ?
     *
     * {@inheritDoc}
     */
    public function has(/*# string */ $key)/*# : bool */
    {
        if (is_string($key) && '#' === substr($key, 0, 1)) {
            return $this->container->has(substr($key, 1));
        }
        return false;
    }
}
