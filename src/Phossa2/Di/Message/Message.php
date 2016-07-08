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

    /**
     * {@inheritDoc}
     */
    protected static $messages = [
        self::DI_CONTAINER_NOTFOUND => 'Container not found',
        self::DI_SERVICE_NOTFOUND => 'Service instance "%s" not found',
    ];
}
