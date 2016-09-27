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

namespace Phossa2\Di\Resolver;

use Phossa2\Di\Container;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Di\Traits\ContainerAwareTrait;
use Phossa2\Config\Interfaces\ConfigInterface;
use Phossa2\Di\Interfaces\ContainerAwareInterface;
use Phossa2\Shared\Delegator\DelegatorAwareInterface;

/**
 * ObjectResolver
 *
 * A config wrapper of container for service instance lookup
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ConfigInterface
 * @see     ContainerAwareInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class ObjectResolver extends ObjectAbstract implements ConfigInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param  Container $container
     * @access public
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container);
    }

    /**
     * Get '#service_id' from the container
     *
     * {@inheritDoc}
     */
    public function get(/*# string */ $id, $default = null)
    {
        if ($this->has($id)) {
            return $this->getRealContainer()->get(static::getRawId($id));
        } else {
            return $default;
        }
    }

    /**
     * Has '#service_id' in the container ?
     *
     * {@inheritDoc}
     */
    public function has(/*# string */ $id)/*# : bool */
    {
        if (static::isServiceId($id)) {
            return $this->getRealContainer()->has(static::getRawId($id));
        }
        return false;
    }

    /**
     * Get container or its delegator
     *
     * @return ContainerInterface
     * @access protected
     */
    protected function getRealContainer()/*# : ContainerInterface */
    {
        $cont = $this->getContainer();

        // get delegator recursively
        if ($cont instanceof DelegatorAwareInterface &&
            $cont->hasDelegator()
        ) {
            $cont = $cont->getDelegator(true);
        }

        return $cont;
    }

    /**
     * Convert '#service_id' to 'service_id'
     *
     * @param  string $serviceId
     * @return string
     * @access protected
     */
    public static function getRawId(/*# string */ $serviceId)/*# : string */
    {
        return substr($serviceId, 1);
    }

    /**
     * Convert 'service_id' to '#service_id'
     *
     * @param  string $rawId
     * @return string
     * @access public
     * @static
     */
    public static function getServiceId(/*# string */ $rawId)/*# : string */
    {
        return '#' . $rawId;
    }

    /**
     * Is $serviceId something like '#service_id' ?
     *
     * @param  mixed $serviceId
     * @return bool
     * @access public
     * @static
     */
    public static function isServiceId($serviceId)/*# : bool */
    {
        if (is_string($serviceId) &&
            isset($serviceId[0]) &&
            '#' == $serviceId[0]
        ) {
            return true;
        }
        return false;
    }
}
