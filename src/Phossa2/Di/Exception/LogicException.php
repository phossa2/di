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

namespace Phossa2\Di\Exception;

/**
 * LogicException for Phossa2\Di
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     \LogicException
 * @see     ExceptionInterface
 * @see     ContainerExceptionInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class LogicException extends \LogicException implements ExceptionInterface, ContainerExceptionInterface
{
}
