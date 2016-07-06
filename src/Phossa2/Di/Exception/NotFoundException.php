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
 * NotFoundException for Phossa2\Di
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ExceptionInterface
 * @see     NotFoundExceptionInterface
 * @see     ContainerExceptionInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class NotFoundException extends \RuntimeException implements ExceptionInterface, NotFoundExceptionInterface, ContainerExceptionInterface
{
}
