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
 * ResolverInterface
 *
 * Definition resolver dealing with instance/parameter/mapping etc.
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ResolverInterface
{
    /**
     * Get $key from resolver
     *
     * Returns NULL if not found
     *
     * @param  string $key key/name
     * @param  string $section section relative to the base node
     * @return mixed
     * @access public
     * @api
     */
    public function get(
        /*# string */ $key,
        /*# string */ $section = ''
    );

    /**
     * Has $key exists in resolver ?
     *
     * @param  string $key key/name
     * @param  string $section section relative to the base node
     * @return bool
     * @access public
     * @api
     */
    public function has(
        /*# string */ $key,
        /*# string */ $section = ''
    )/*# : bool */;

    /**
     * Set/replace/delete $key in resolver
     *
     * ```php
     * // set a parameter
     * $resolver->set('cache.root', '/var/tmp');
     *
     * // set with a reference
     * $resolver->set('cache.root', '${tmp.dir}');
     *
     * // set with an array
     * $resolver->set('cache', [
     *     'root' => '/var/tmp',
     *     'name' => 'session_cache',
     *     'lifetime' => 86400
     * ]);
     * ```
     *
     * @param  string $key key/name
     * @param  mixed $value the value, NULL is allowed
     * @param  string $section section relative to the base node
     * @return $this
     * @access public
     * @api
     */
    public function set(
        /*# string */ $key,
        $value,
        /*# string */ $section = ''
    );

    /**
     * Get the $key in 'service' section
     *
     * @param  string $key key/name
     * @return mixed
     * @access public
     * @api
     */
    public function getService(/*# string */ $key);

    /**
     * Has the $key in 'service' section
     *
     * @param  string $key key/name
     * @return bool
     * @access public
     * @api
     */
    public function hasService(/*# string */ $key)/*# : bool */;

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
     * ```
     *
     * @param  string $key key/name
     * @param  mixed $definition classname/callable/array/object etc.
     * @param  array $arguments constructor/callable arguments
     * @return $this
     * @access public
     * @api
     */
    public function setService(
        /*# string */ $key,
        $definition,
        array $arguments = []
    );

    /**
     * Get the $key in 'mapping' section
     *
     * @param  string $key key/name
     * @return mixed string or callable etc.
     * @access public
     * @api
     */
    public function getMapping(/*# string */ $key);

    /**
     * Has the $key in 'mapping' section
     *
     * @param  string $key key/name
     * @return bool
     * @access public
     * @api
     */
    public function hasMapping(/*# string */ $key)/*# : bool */;

    /**
     * Map an interface to a classname
     *
     * You may also map classname to child classname, map interface or
     * classname to a service id reference '${#service_id}' or a parameter
     * reference '${parameter.name}
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
     * @api
     */
    public function setMapping(/*# string */ $from, $to);

    /**
     * Turn on/off autowiring (auto classname resolving)
     *
     * @param  bool $on true or false
     * @return $this
     * @access public
     * @api
     */
    public function autoWiring(/*# bool */ $on = true);
}
