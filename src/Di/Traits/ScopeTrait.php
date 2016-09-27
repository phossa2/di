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

/**
 * ScopeTrait
 *
 * Implmentation of ScopeInterface.
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ScopeInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait ScopeTrait
{
    use ContainerHelperTrait;

    /**
     * default scope for objects
     *
     * @var    string
     * @access protected
     */
    protected $default_scope = ScopeInterface::SCOPE_SHARED;

    /**
     * Split 'service_id@scope' into ['service_id', 'scope']
     *
     * if no scope found, use ''
     *
     * @param  string $id
     * @return array [$id, $scope]
     * @access protected
     */
    protected function splitId(/*# string */ $id)/*# : array */
    {
        if (false !== strpos($id, '@')) {
            return explode('@', $id, 2);
        } else {
            return [$id, ''];
        }
    }

    /**
     * Return the raw id without scope
     *
     * @param  string $id
     * @return string
     * @access protected
     */
    protected function idWithoutScope(/*# string */ $id)/*# : string */
    {
        return $this->splitId($id)[0];
    }

    /**
     * Get the scope part of $id
     *
     * @param  string $id
     * @return string
     * @access protected
     */
    protected function getScopeOfId(/*# string */ $id)/*# : string */
    {
        return $this->splitId($id)[1];
    }

    /**
     * Append a scope to the $id, if old scope exists, replace it
     *
     * @param  string $id
     * @param  string $scope
     * @return string
     * @access protected
     */
    protected function scopedId(
        /*# string */ $id,
        /*# string */ $scope
    )/*# : string */ {
        return $this->idWithoutScope($id) . '@' . $scope;
    }

    /**
     * Returns the raw id and the scope base on default or defined
     *
     * @param  string $id
     * @return array [rawId, scope]
     * @access protected
     */
    protected function scopedInfo(/*# string */ $id)/*# : array */
    {
        // split into raw id and scope (if any)
        list($rawId, $scope) = $this->splitId($id);

        // use the default scope if no scope given
        if (empty($scope)) {
            // use the default
            $scope = $this->default_scope;

            // honor predefined scope over the default
            $def = $this->getResolver()->getService($rawId);
            if (is_array($def) && isset($def['scope'])) {
                $scope = $def['scope'];
            }
        }

        return [$rawId, $scope];
    }

    /**
     * Append scope to data
     *
     * @param  mixed $data
     * @param  string $scope
     * @return array
     * @access protected
     */
    protected function scopedData($data, /*# string */ $scope)/*# : array */
    {
        if (is_array($data) && isset($data['class'])) {
            $data['scope'] = $scope;
        } else {
            $data = ['class' => $data, 'scope' => $scope];
        }
        return $data;
    }
}
