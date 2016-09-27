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
 * AutoWiringInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface AutoWiringInterface
{
    /**
     * Turn on/off autowiring (auto classname resolving)
     *
     * @param  bool $flag true or false
     * @return $this
     * @access public
     * @api
     */
    public function auto(/*# bool */ $flag = true);

    /**
     * Is autowiring turned on ?
     *
     * @return bool
     * @access public
     * @api
     */
    public function isAuto()/*# : bool */;
}
