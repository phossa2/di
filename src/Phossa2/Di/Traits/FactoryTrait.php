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

use Phossa2\Di\Scope\ScopeTrait;
use Phossa2\Di\Exception\LogicException;

/**
 * FactoryTrait
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait FactoryTrait
{
    use ScopeTrait;

    /**
     * for loop detection
     *
     * @var    array
     * @access protected
     */
    protected $loop = [];

    /**
     * Full scope info
     *
     * @param  sting $id
     * @return array
     * @access protected
     */
    protected function fullScopeInfo(/*# string */ $id)/*# : array */
    {
        list($rawId, $scope) = $this->scopedInfo($id);

        // if $scope is upper level '#service_id'
        if (isset($this->loop[$scope])) {
            $scope .= '_' . $this->loop[$scope];
        }

        return [$rawId, $this->scopedId($rawId, $scope), $scope];
    }

    /**
     * Create the instance
     *
     * @param  string $rawId
     * @param  array $arguments
     * @return object
     * @throws LogicException if anything goes wrong
     * @access protected
     */
    protected function factoryInstance(/*# string */ $rawId, array $arguments)
    {
        static $counter = 0;

        // service id
        $serviceId = $this->getServiceId($rawId);

        // loop found
        if (isset($this->loop[$serviceId])) {

        }

        // set loop marker
        $this->loop[$serviceId] = ++$counter;

        // create the service object
        $obj = $this->createObject($rawId, $arguments);

        // remove loop marker
        unset($this->loop[$serviceId]);

        return $obj;
    }

    /**
     * Append '#' to rawId, representing a service object id
     *
     * @param  string $rawId
     * @return string
     * @access protected
     */
    protected function getServiceId(/*# string */ $rawId)/*# : string */
    {
        return '#' . $rawId;
    }
}
