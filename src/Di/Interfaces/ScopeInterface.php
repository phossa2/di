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

namespace Phossa2\Di\Interfaces;

/**
 * ScopeInterface
 *
 * Managing objects in scopes, either static(fixed) scope or dynamic scope
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ScopeInterface
{
    /**
     * Shared scope: share instance each time
     *
     * @var    string
     * @access public
     */
    const SCOPE_SHARED = '__SHARED__';

    /**
     * Single scope: create new instance each time
     *
     * @var    string
     * @access public
     */
    const SCOPE_SINGLE = '__SINGLE__';

    /**
     * Set container's default scope to either __SHARED__ or __SINGLE__
     *
     * @param  bool $flag shared or not
     * @return $this
     * @access public
     * @api
     */
    public function share(/*# bool */ $flag = true);
}
