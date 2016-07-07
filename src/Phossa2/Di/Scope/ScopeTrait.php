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
        $parts = explode('@', $id, 2);
        return isset($parts[1]) ? $parts : [$parts[0], ''];
    }

    /**
     * Append a scope to the $id, if old scope exists, replace it
     *
     * @param  string $id
     * @param  string $scope
     * @return string
     * @access protected
     */
    protected function appendScopeToId(
        /*# string */ $id,
        /*# string */ $scope
    )/*# : string */ {
        list($id, $oscope) = $this->splitId($id);
        return $id . '@' . $scope;
    }
}
