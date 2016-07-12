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

use Phossa2\Di\Exception\NotFoundException;

/**
 * FactoryAwareInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface FactoryAwareInterface
{
    /**
     * Inject the Factory
     *
     * @param  FactoryInterface $factory
     * @return $this
     * @access public
     * @api
     */
    public function setFactory(FactoryInterface $factory);

    /**
     * Get the Factory
     *
     * @return FactoryInterface
     * @access public
     * @throws NotFoundException if factory not found
     * @api
     */
    public function getFactory()/*# : FactoryInterface */;

    /**
     * Has the Factory ?
     *
     * @return bool
     * @access public
     * @api
     */
    public function hasFactory()/*# : bool */;
}
