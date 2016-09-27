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

namespace Phossa2\Di;

use Phossa2\Di\Message\Message;
use Phossa2\Di\Traits\ArrayAccessTrait;
use Phossa2\Shared\Base\ObjectAbstract;
use Interop\Container\ContainerInterface;
use Phossa2\Di\Exception\RuntimeException;
use Phossa2\Di\Exception\NotFoundException;
use Phossa2\Di\Interfaces\DelegatorInterface;
use Phossa2\Config\Interfaces\WritableInterface;
use Phossa2\Config\Traits\DelegatorWritableTrait;

/**
 * Delegator
 *
 * A writable and array accessable container delegator
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ObjectAbstract
 * @see     DelegatorInterface
 * @see     WritableInterface
 * @see     \ArrayAccess
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Delegator extends ObjectAbstract implements DelegatorInterface, \ArrayAccess, WritableInterface
{
    use ArrayAccessTrait, DelegatorWritableTrait;

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if ($this->hasInLookup($id)) {
            return $this->getFromLookup($id);
        } else {
            throw new NotFoundException(
                Message::get(Message::DI_SERVICE_NOTFOUND, $id),
                Message::DI_SERVICE_NOTFOUND
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return $this->hasInLookup($id);
    }

    /**
     * {@inheritDoc}
     */
    public function set(/*# string */ $id, $value)/*# : bool */
    {
        if ($this->isWritable()) {
            return $this->writable->set($id, $value);
        } else {
            throw new RuntimeException(
                Message::get(Message::DI_CONTAINER_READONLY, $id),
                Message::DI_CONTAINER_READONLY
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addContainer(ContainerInterface $container)
    {
        return $this->addRegistry($container);
    }

    /**
     * {@inheritDoc}
     */
    protected function hasInRegistry(
        $registry,
        /*# string */ $key
    )/*# : bool */ {
        /* @var $registry ContainerInterface */
        return $registry->has($key);
    }

    /**
     * {@inheritDoc}
     */
    protected function getFromRegistry(
        $registry,
        /*# string */ $key
    ) {
        /* @var $registry ContainerInterface */
        return $registry->get($key);
    }
}
