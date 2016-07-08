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

namespace Phossa2\Di\Scope;

use Phossa2\Di\Definition\ResolverInterface;

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
        $this->default_scope = (bool) $shared ? ScopeInterface::SCOPE_SHARED :
            ScopeInterface::SCOPE_SINGLE;
        return $this;
    }

    /**
     * Split 'service_id@scope' into ['service_id', 'scope']
     *
     * if no scope part, returns '' as the scope
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
     * Returns the raw id (without scope) and the scope
     *
     * @param  string $id
     * @return array [rawId, scope]
     * @access protected
     */
    protected function scopedInfo(/*# string */ $id)/*# : array */
    {
        // split into raw id and scope (if any)
        list($rawId, $scope) = $this->splitId($id);

        // get the default scope for $rawId
        if (empty($scope)) {
            $definition = $this->getResolver()->getServiceDefinition($rawId);
            if (isset($definition['scope'])) {
                $scope = $definition['scope'];
            } else {
                $scope = $this->default_scope;
            }
        }

        return [$rawId, $scope];
    }

    /**
     * Is $scopedId has __SINGLE__ scope ?
     *
     * @param  string $scopedId
     * @return bool
     * @access protected
     */
    protected function isSingleScoped(/*# string */ $scopedId)/*# : bool */
    {
        return ScopeInterface::SCOPE_SINGLE === $this->splitId($scopedId)[1];
    }

    /**
     * @return ResolverInterface
     */
    abstract public function getResolver()/*# : ResolverInterface */;
}
