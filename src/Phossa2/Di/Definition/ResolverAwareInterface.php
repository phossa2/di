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

/**
 * ResolverAwareInterface
 *
 * Able to use ResolverInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ResolverAwareInterface
{
    /**
     * Get the definition resolver
     *
     * @return ResolverInterface
     * @access public
     * @api
     */
    public function getResolver()/*# : ResolverInterface */;

    /**
     * Resolve reference in the string or array
     *
     * - resolve string: '${system.tmpdir}/session'
     * - resolve service: '${#cache}'
     * - resolve array: ['${#cache}', 'getFromCache']
     *
     * @param  mixed &$toResolve
     * @return $this
     * @access public
     * @api
     */
    public function resolve(&$toResolve);
}
