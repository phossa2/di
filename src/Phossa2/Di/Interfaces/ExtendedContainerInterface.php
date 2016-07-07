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

/**
 * ExtendedContainerInterface
 *
 * Couple of extended funtionalities
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface ExtendedContainerInterface
{
    /**
     * Get a NEW service instance even if it was defined as shared
     *
     * Arguments can have references as follows
     *
     * ```php
     * // reference other service
     * $cache = $container->one('cache', ['${#cache_driver}']);
     *
     * // parameters are allowed
     * $cache = $container->one('cache', ['${#cache_driver}', '${cache.id}']);
     * ```
     *
     * @param  string $id service id
     * @param  array $arguments (optional) arguments
     * @return object
     * @throws LogicException if anything goes wrong
     * @access public
     * @api
     */
    public function one(/*# string */ $id, array $arguments = []);

    /**
     * Execute a callable, expands its arguments
     *
     * $callable can be a pseudo callable as follows,
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
     * @throws \Exception if execution goes wrong
     * @access public
     * @api
     */
    public function run($callable, array $arguments = []);

    /**
     * Execute/run the callables in batch mode
     *
     * ```php
     * $methods = [
     *     ['func' => ['${#logger}', 'warning'], 'args' => []],
     *     ...
     * ];
     * ```
     * @param  array $methods
     * @return $this
     * @access public
     * @api
     */
    public function batch(array $methods);
}
