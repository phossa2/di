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

use Interop\Container\ContainerInterface;
use Phossa2\Di\Exception\NotFoundException;

/**
 * ContainerAwareInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ContainerAwareInterface
{
    /**
     * Inject the container
     *
     * @param  ContainerInterface $container
     * @return $this
     * @access public
     * @api
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the container
     *
     * @return ContainerInterface
     * @access public
     * @throws NotFoundException if container not found
     * @api
     */
    public function getContainer()/*# : ContainerInterface */;

    /**
     * Has the container ?
     *
     * @return bool
     * @access public
     * @api
     */
    public function hasContainer()/*# : bool */;
}
