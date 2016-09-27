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

use Phossa2\Di\Interfaces\ResolverInterface;

/**
 * ResolverAwareTrait
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
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
     * Get the resolver
     *
     * @return ResolverInterface
     * @access protected
     */
    protected function getResolver()/*# : ResolverInterface */
    {
        return $this->resolver;
    }

    /**
     * Set the resolver
     *
     * @param  ResolverInterface $resolver
     * @return $this
     * @access protected
     */
    protected function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }
}
