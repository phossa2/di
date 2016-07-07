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
 * Definition resolver dealing with service/parameter/mapping definitions
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ResolverInterface
{
    /**
     * Get the definition from resolver
     *
     * Returns NULL if not found
     *
     * @param  string $key
     * @param  string $section section related to base node
     * @return mixed
     * @access public
     * @api
     */
    public function getDefinition(
        /*# string */ $key,
        /*# string */ $section = ''
    );

    /**
     * Has the definition with name $key
     *
     * @param  string $key parameter key/name
     * @param  string $section section related to base node
     * @return bool
     * @access public
     * @api
     */
    public function hasDefinition(
        /*# string */ $key,
        /*# string */ $section = ''
    )/*# : bool */;

    /**
     * Set/replace/delete parameter definition(s) in resolver
     *
     * ```php
     * // set a parameter
     * $resolver->setDefinition('cache.root', '/var/tmp');
     *
     * // set with a reference
     * $resolver->setDefinition('cache.root', '${tmp.dir}');
     *
     * // set with an array
     * $resolver->setDefinition('cache', [
     *     'root' => '/var/tmp',
     *     'name' => 'session_cache',
     *     'lifetime' => 86400
     * ]);
     * ```
     *
     * @param  string $key parameter key/name
     * @param  mixed $value NULL is allowed
     * @param  string $section section related to base node
     * @return $this
     * @access public
     * @api
     */
    public function setDefinition(
        /*# string */ $key,
        $value,
        /*# string */ $section = ''
    );

    /**
     * Get the service definition for a service id
     *
     * @param  string $serviceId
     * @return mixed
     * @access public
     * @api
     */
    public function getServiceDefinition(/*# string */ $serviceId);

    /**
     * Has the service definition with $serviceId
     *
     * @param  string $serviceId service id
     * @return bool
     * @access public
     * @api
     */
    public function hasServiceDefinition(/*# string */ $serviceId)/*# : bool */;

    /**
     * Add/overwrite service definition(s)
     *
     * ```php
     * // define a service with classname
     * $resolver->setServiceDefinition('cache', 'Phossa2\\Cache\\CachePool');
     *
     * // define a service with a closue
     * $resolver->setServiceDefinition('cache', function() {
     *     return new \Phossa2\Cache\CachePool();
     * });
     *
     * // define with class name and constructor arguments
     * $resolver->setServiceDefinition(
     *     'cache', 'Phossa2\\Cache\\CachePool', ['${@driver}']
     * );
     *
     * // alias, pointing to another service
     * $resolver->setServiceDefinition('sessionCache', '${@globalCache}');
     *
     * // define service with a (pseudo) callable
     * $resolver->setServiceDefinition('logger', [${@event}, 'getLogger']);
     * ```
     *
     * @param  string $serviceId service id
     * @param  mixed $definition classname/callable/array/object etc.
     * @param  array $arguments constructor/callable arguments
     * @return $this
     * @access public
     * @api
     */
    public function setServiceDefinition(
        /*# string */ $serviceId,
        $definition,
        array $arguments = []
    );

    /**
     * Get the mapping definition for $key
     *
     * @param  string $key mapping name
     * @return mixed string or callable etc.
     * @access public
     * @api
     */
    public function getMappingDefinition(/*# string */ $key);

    /**
     * Has the mapping definition for $key
     *
     * @param  string $key mapping name
     * @return bool
     * @access public
     * @api
     */
    public function hasMappingDefinition(/*# string */ $key)/*# : bool */;

    /**
     * Map an interface to a classname
     *
     * You may also map classname to child classname, map interface or
     * classname to a service id reference '${@service_id}' or a parameter
     * reference '${parameter.name}
     *
     * ```php
     * // map a interface => a classname
     * $resolver->setMappingDefinition(
     *     'Phossa2\\Cache\\CachePoolInterface', // NO leading backslash
     *     'Phossa2\\Cache\\CachePool'
     * );
     *
     * // map a interface => service reference
     * $resolver->setMappingDefinition(
     *     'Phossa2\\Cache\\CachePoolInterface',
     *     '${@cache}'
     * );
     *
     * // map a interface => a parameter reference
     * $resolver->setMappingDefinition(
     *     'Phossa2\\Cache\\CachePoolInterface',
     *     '${cache.class}'
     * );
     *
     * // map to a callable
     * $resolver->setMappingDefinition(
     *     'Phossa2\\Cache\\CachePoolInterface',
     *     functino() {
     *         return new Phossa2\Cache\Cache();
     *     }
     * );
     * ```
     *
     * @param  string $from interface or class name
     * @param  $to class name/${@service_id}/${parameter} or even callback
     * @return $this
     * @access public
     * @api
     */
    public function setMappingDefinition(/*# string */ $from, $to);

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
