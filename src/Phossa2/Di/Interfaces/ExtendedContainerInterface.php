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
 * Extended funtionalities for a container
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ExtendedContainerInterface
{
    /**
     * Get a NEW service instance IF scope is NOT explicitly defined
     *
     * Explicitly defined means: ['class' => ..., 'scope' => '__SHARED__']
     *
     * ```php
     * // get a new cache instance using shared cache driver
     * $cache = $container->one('cache', ['${#cache_driver}']);
     * ```
     *
     * @param  string $id service id
     * @param  array $arguments (optional) new arguments for the constructor
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
     * @throws LogicException if container goes wrong
     * @throws RuntimeException if execution goes wrong
     * @access public
     * @api
     */
    public function run($callable, array $arguments = []);

    /**
     * Map an interface or classname to something else.
     *
     * e.g. to another classname, reference or callback etc.
     *
     * @param  string $from
     * @param  mixed $to
     * @return $this
     * @access public
     * @api
     */
    public function map(/*# string */ $from, $to);

    /**
     * Resolve all references in the $toResolve (either an array or string)
     *
     * @param  mixed &$toResolve
     * @return $this
     * @access public
     * @api
     */
    public function resolve(&$toResolve);
}
