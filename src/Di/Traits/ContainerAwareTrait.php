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

use Phossa2\Di\Message\Message;
use Interop\Container\ContainerInterface;
use Phossa2\Di\Exception\NotFoundException;
use Phossa2\Di\Interfaces\ContainerAwareInterface;

/**
 * ContainerAwareTrait
 *
 * Implementation of ContainerAwareInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ContainerAwareInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait ContainerAwareTrait
{
    /**
     * @var    ContainerInterface
     * @access protected
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getContainer()/*# : ContainerInterface */
    {
        if ($this->hasContainer()) {
            return $this->container;
        }
        throw new NotFoundException(
            Message::get(Message::DI_CONTAINER_NOTFOUND),
            Message::DI_CONTAINER_NOTFOUND
        );
    }

    /**
     * {@inheritDoc}
     */
    public function hasContainer()/*# : bool */
    {
        return null !== $this->container;
    }
}
