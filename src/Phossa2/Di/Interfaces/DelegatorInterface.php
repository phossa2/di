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
use Phossa2\Shared\Reference\DelegatorInterface;
use Phossa2\Config\Interfaces\WritableInterface;

/**
 * DelegatorInterface
 *
 * For container library
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ContainerInterface
 * @see     DelegatorInterface
 * @see     WritableInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface DelegatorInterface extends DelegatorInterface, ContainerInterface, WritableInterface
{
    /**
     * Add container
     *
     * @param  ContainerInterface $container
     * @return $this
     * @access public
     * @api
     */
    public function addContainer(ContainerInterface $container);
}
