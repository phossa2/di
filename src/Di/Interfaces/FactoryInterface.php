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
 * FactoryInterface
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface FactoryInterface
{
    /**
     * Create the object
     *
     * @param  string $rawId
     * @param  array $arguments unresolved arguments
     * @return object
     * @throws LogicException if instantiation goes wrong
     * @throws RuntimeException if execution methods goes wrong
     * @access public
     */
    public function createInstance(/*# string */ $rawId, array $arguments);

    /**
     * Execute a callable with arguments (resolved already)
     *
     * @param  callable|array|object $callable callable
     * @param  array $arguments
     * @return mixed
     * @throws RuntimeException if anything goes wrong
     * @access public
     */
    public function executeCallable($callable, array $arguments = []);

    /**
     * Execute batch of methdos defined as
     *
     * [
     *   [method, arguments],
     *   ...
     * ]
     *
     * or
     * [
     *   'section' => [
     *     [method, arguments],
     *     ...
     *   ],
     *   ...
     * ]
     *
     * @param  array $methods
     * @param  object|null $object object used to construct callable
     * @throws RuntimeException if something goes wrong
     * @access public
     */
    public function executeMethodBatch(array $methods, $object = null);
}
