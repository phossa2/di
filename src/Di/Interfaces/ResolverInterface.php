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
 * ResolverInterface
 *
 * Resolveing instance/parameter/mapping etc.
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ResolverInterface
{
    /**
     * Get the $id in 'service' section
     *
     * Returns all the services if $id === ''
     *
     * @param  string $id key/name
     * @return mixed
     * @access public
     */
    public function getService(/*# string */ $id = '');

    /**
     * Has the $id in 'service' section
     *
     * @param  string $id key/name
     * @return bool
     * @access public
     */
    public function hasService(/*# string */ $id = '')/*# : bool */;

    /**
     * Add/overwrite in service section
     *
     * ```php
     * // define a service with classname
     * $resolver->setService('cache', 'Phossa2\\Cache\\CachePool');
     *
     * // define a service with a closue
     * $resolver->setService('cache', function() {
     *     return new \Phossa2\Cache\CachePool();
     * });
     *
     * // define with class name and constructor arguments
     * $resolver->setService(
     *     'cache', 'Phossa2\\Cache\\CachePool', ['${#driver}']
     * );
     *
     * // alias, pointing to another service
     * $resolver->setService('sessionCache', '${#globalCache}');
     *
     * // define service with a (pseudo) callable
     * $resolver->setService('logger', [${#event}, 'getLogger']);
     *
     * // define service with an array
     * $resolver->setService('cache', [
     *     'class' => 'Phossa2\\Cache\\CachePool',
     *     'args'  => ['${#driver}']
     * ]);
     * ```
     *
     * @param  string $id key/name
     * @param  mixed $definition classname/callable/array/object etc.
     * @param  array $args constructor/callable arguments
     * @return bool true on success or false on failure
     * @throws LogicException if not writable
     * @access public
     */
    public function setService(
        /*# string */ $id,
        $definition,
        array $args = []
    )/*# : bool */;

    /**
     * Generate new id base on base node and section
     *
     * @param  string $id
     * @param  string $section
     * @return string
     * @access public
     */
    public function getSectionId(
        /*# string */ $id,
        /*# string */ $section = 'service'
    )/*# : string */;
}
