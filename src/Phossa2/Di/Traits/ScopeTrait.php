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
use Phossa2\Di\Definition\ResolverAwareTrait;

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
    use ResolverAwareTrait;

    /**
     * default scope for objects
     *
     * @var    string
     * @access protected
     */
    protected $default_scope = ScopeInterface::SCOPE_SHARED;

    /**
     * @inheritDoc
     */
    public function share(/*# bool */ $shared = true)
    {
        $this->default_scope = (bool) $shared ?
            ScopeInterface::SCOPE_SHARED :
            ScopeInterface::SCOPE_SINGLE ;
        return $this;
    }

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
        return $this->splitId($id)[0] . '@' . $scope;
    }

    /**
     * Returns the raw id and the calculated scope
     *
     * @param  string $id
     * @return array [rawId, scope]
     * @access protected
     */
    protected function scopedInfo(/*# string */ $id)/*# : array */
    {
        // split into raw id and scope (if any)
        list($rawId, $scope) = $this->splitId($id);

        // if empty, use the default scope
        $scope = empty($scope) ? $this->default_scope : $scope;

        // honor forced scope
        $definition = $this->getResolver()->getService($rawId);
        if (is_array($definition) && isset($definition['scope'])) {
            $scope = $definition['scope'];
        }

        return [$rawId, $scope];
    }
}
