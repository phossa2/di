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

namespace Phossa2\Di\Message;

use Phossa2\Shared\Message\Message as BaseMessage;

/**
 * Mesage class for Phossa2\Di
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     \Phossa2\Shared\Message\Message
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Message extends BaseMessage
{
    /*
     * Container not found
     */
    const DI_CONTAINER_NOTFOUND = 1607061007;

    /*
     * Service instance "%s" not found
     */
    const DI_SERVICE_NOTFOUND = 1607061008;

    /*
     * Container loop detected for "%s"
     */
    const DI_LOOP_DETECTED = 1607061009;

    /*
     * Parameter "%s" not matched
     */
    const DI_PARAMETER_NOTFOUND = 1607061010;

    /*
     * Resolver not found
     */
    const DI_RESOLVER_NOTFOUND = 1607061011;

    /*
     * Factory not found
     */
    const DI_FACTORY_NOTFOUND = 1607061012;

    /*
     * Write to readonly container with "%s"
     */
    const DI_CONTAINER_READONLY = 1607061013;

    /*
     * Parameter "%s" mismatched for method "%s"
     */
    const DI_PARAMETER_MISMATCH = 1607061014;

    /*
     * Unknown dependent class or interface "%s"
     */
    const DI_CLASS_UNKNOWN = 1607061015;

    /*
     * Bad callable "%s" found
     */
    const DI_CALLABLE_BAD = 1607061016;

    /**
     * {@inheritDoc}
     */
    protected static $messages = [
        self::DI_CONTAINER_NOTFOUND => 'Container not found',
        self::DI_SERVICE_NOTFOUND => 'Service instance "%s" not found',
        self::DI_LOOP_DETECTED => 'Container loop detected for "%s"',
        self::DI_PARAMETER_NOTFOUND => 'Parameter "%s" not matched',
        self::DI_RESOLVER_NOTFOUND => 'Resolver not found',
        self::DI_FACTORY_NOTFOUND => 'Factory not found',
        self::DI_CONTAINER_READONLY => 'Write to readonly container with "%s"',
        self::DI_PARAMETER_MISMATCH => 'Parameter "%s" mismatched for method "%s"',
        self::DI_CLASS_UNKNOWN => 'Unknown dependent class or interface "%s"',
        self::DI_CALLABLE_BAD => 'Bad callable "%s" found',
    ];
}
