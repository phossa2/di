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

namespace Phossa2\Di\Definition;

/**
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
     * @access private
     */
    private $resolver;

    /**
     * {@inheritDoc}
     * @return ResolverInterface
     */
    public function getResolver()/*# : ResolverInterface */
    {
        return $this->resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(&$toResolve)
    {
        if (!empty($toResolve) &&
            (is_array($toResolve) || is_string($toResolve))
        ) {
            $this->getResolver()->resolve($toResolve);
        }
    }

    /**
     * Set the definition resolver
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
