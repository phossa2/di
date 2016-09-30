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

namespace Phossa2\Di;

use Phossa2\Di\Message\Message;
use Phossa2\Shared\Base\StaticAbstract;
use Interop\Container\ContainerInterface;
use Phossa2\Di\Exception\RuntimeException;
use Phossa2\Di\Exception\NotFoundException;

/**
 * Service
 *
 * Provides a service locator building around the container
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.1.0
 * @since   2.1.0 added
 */
class Service extends StaticAbstract
{
    /**
     * @var    ContainerInterface
     * @access protected
     * @staticvar
     */
    protected static $container;

    /**
     * Locate a service from the container
     *
     * ```php
     * // the global config object
     * $config = Service::config();
     *
     * // the container
     * $container = Service::container();
     * ```
     *
     * @param  string $method object id actually
     * @param  array $params
     * @return object
     * @throws NotFoundException if container not set or object not found
     * @throws RuntimeException if object instantiation error
     * @access public
     * @api
     */
    public static function __callstatic(/*# string */ $method, array $params)
    {
        if (static::$container) {
            // append scope if provided
            if (!isset($params[0])) {
                $method .= '@' . $params[0];
            }
            return static::$container->get($method);
        }

        // container not set yet
        throw new NotFoundException(
            Message::get(Message::DI_CONTAINER_NOTFOUND),
            Message::DI_CONTAINER_NOTFOUND
        );
    }

    /**
     * Set container
     *
     * @param  ContainerInterface $container
     * @access public
     * @api
     */
    public function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }
}
