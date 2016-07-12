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
use Phossa2\Di\Interfaces\ResolverInterface;
use Phossa2\Di\Interfaces\ResolverAwareInterface;

/**
 * ResolverAwareTrait
 *
 * Implementation of ResolverAwareInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ResolverAwareInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait ResolverAwareTrait
{
    /**
     * @var    ResolverInterface
     * @access protected
     */
    protected $resolver;

    /**
     * {@inheritDoc}
     */
    public function getResolver()/*# : ResolverInterface */
    {
        if ($this->hasResolver()) {
            return $this->resolver;
        }
        throw new NotFoundException(
            Message::get(Message::DI_RESOLVER_NOTFOUND),
            Message::DI_RESOLVER_NOTFOUND
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasResolver()/*# : bool */
    {
        return null !== $this->resolver;
    }
}
