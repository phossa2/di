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
 * ReferenceResolvInterface
 *
 * resolving references
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ReferenceResolveInterface
{
    /**
     * Resolve all references in the $toResolve (either an array or string)
     *
     * @param  mixed &$toResolve
     * @return $this
     * @access public
     * @api
     */
    public function resolve(&$toResolve);
}
