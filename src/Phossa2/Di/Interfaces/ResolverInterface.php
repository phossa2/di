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
     * Resolve all references in the $toResolve (either an array or string)
     *
     * @param  mixed &$toResolve
     * @return $this
     * @access public
     */
    public function resolve(&$toResolve);

    /**
     * Set the object resolver
     *
     * @access public
     * @api
     */
    public function setObjectResolver();

    /**
     * Get the $id in section
     *
     * @param  string $id key/name
     * @param  string $section section relative to the base
     * @return mixed
     * @access public
     */
    public function getInSection(/*# string */ $id, /*# string */ $section);

    /**
     * Has the $id in section
     *
     * @param  string $id key/name
     * @param  string $section section relative to the base
     * @return bool
     * @access public
     */
    public function hasInSection(
        /*# string */ $id,
        /*# string */ $section
    )/*# : bool */;

    /**
     * Add/overwrite in section
     *
     * @param  string $id key/name
     * @param  string $section section relative to the base
     * @param  mixed $value
     * @return $this
     * @access public
     */
    public function setInSection(
        /*# string */ $id,
        /*# string */ $section,
        $value
    );

    /**
     * Get the $id in 'service' section
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
     * @param  array $arguments constructor/callable arguments
     * @return $this
     * @access public
     */
    public function setService(
        /*# string */ $id,
        $definition,
        array $arguments = []
    );

    /**
     * Get the $id in 'mapping' section
     *
     * @param  string $id key/name
     * @return mixed string or callable etc.
     * @access public
     */
    public function getMapping(/*# string */ $id = '');

    /**
     * Has the $id in 'mapping' section
     *
     * @param  string $id key/name
     * @return bool
     * @access public
     */
    public function hasMapping(/*# string */ $id = '')/*# : bool */;

    /**
     * Map an interface to a classname
     *
     * ```php
     * // map a interface => a classname
     * $resolver->setMapping(
     *     'Phossa2\\Cache\\CachePoolInterface', // NO leading backslash
     *     'Phossa2\\Cache\\CachePool'
     * );
     *
     * // map a interface => service reference
     * $resolver->setMapping(
     *     'Phossa2\\Cache\\CachePoolInterface',
     *     '${#cache}'
     * );
     *
     * // map a interface => a parameter reference
     * $resolver->setMapping(
     *     'Phossa2\\Cache\\CachePoolInterface',
     *     '${cache.class}'
     * );
     *
     * // map to a callable
     * $resolver->setMapping(
     *     'Phossa2\\Cache\\CachePoolInterface',
     *     functino() {
     *         return new Phossa2\Cache\Cache();
     *     }
     * );
     * ```
     *
     * @param  string $from interface or class name
     * @param  $to class name/${#service_id}/${parameter} or even callback
     * @return $this
     * @access public
     */
    public function setMapping(/*# string */ $from, $to);
}
