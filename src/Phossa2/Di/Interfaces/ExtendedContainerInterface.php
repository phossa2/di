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

use Phossa2\Di\Exception\LogicException;
use Phossa2\Di\Exception\RuntimeException;

/**
 * ExtendedContainerInterface
 *
 * Extended funtionalities for a ContainerInface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     AutoWiringInterface
 * @see     ReferenceResolveInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ExtendedContainerInterface extends AutoWiringInterface, ReferenceResolveInterface
{
    /**
     * Get a NEW service instance
     *
     * ```php
     * // get a new cache instance using shared cache driver
     * $cache = $container->one('cache', ['${#cache_driver}']);
     * ```
     *
     * @param  string $id service id
     * @param  array $arguments (optional) arguments for the constructor
     * @return object
     * @throws LogicException if anything goes wrong
     * @access public
     * @api
     */
    public function one(/*# string */ $id, array $arguments = []);

    /**
     * Execute a callable(maybe pseudo) with the given arguments
     *
     * ```php
     * // pseudo callable using service reference string
     * $container->run(['${#cache}', 'setLogger'], ['${#logger}']);
     *
     * // method can be a parameter
     * $container->run([$cache, '${log.setter}'], [$logger]);
     * ```
     *
     * @param  callable|array $callable
     * @param  array $arguments (optional) arguments
     * @return mixed
     * @throws LogicException if container resolving goes wrong
     * @throws RuntimeException if execution goes wrong
     * @access public
     * @api
     */
    public function run($callable, array $arguments = []);

    /**
     * Set up a parameter, later can be used as reference ${parameter}
     *
     * @param  string $name
     * @param  mixed $value
     * @return bool true on success, false on failure
     * @access public
     * @api
     */
    public function param(/*# string */ $name, $value)/*# : bool */;

    /**
     * Register an object into the container without executing common methods
     *
     * $name may contain scope name
     *
     * @param  string $id
     * @param  object|string $object or a valid service reference
     * @return bool true on success, false on failure
     * @access public
     * @api
     */
    public function alias(/*# string */ $id, $object)/*# : bool */;
}
