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
use Phossa2\Di\Exception\NotFoundException;
use Phossa2\Di\Interfaces\FactoryInterface;
use Phossa2\Di\Interfaces\FactoryAwareInterface;

/**
 * FactoryAwareTrait
 *
 * Implementation of FactoryAwareInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     FactoryAwareInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait FactoryAwareTrait
{
    /**
     * @var    FactoryInterface
     * @access protected
     */
    protected $factory;

    /**
     * {@inheritDoc}
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFactory()/*# : FactoryInterface */
    {
        if ($this->hasFactory()) {
            return $this->factory;
        }
        throw new NotFoundException(
            Message::get(Message::DI_FACTORY_NOTFOUND),
            Message::DI_FACTORY_NOTFOUND
        );
    }

    /**
     * {@inheritDoc}
     */
    public function hasFactory()/*# : bool */
    {
        return null !== $this->factory;
    }
}
