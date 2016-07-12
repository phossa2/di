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

namespace Phossa2\Di\Traits;

use Phossa2\Config\Traits\WritableTrait;

/**
 * ArrayAccessTrait
 *
 * Array access for container
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     \ArrayAccess
 * @version 2.0.0
 * @since   2.0.0 added
 */
trait ArrayAccessTrait
{
    use WritableTrait;

    public function offsetExists($offset)/*# : bool */
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->offsetSet($offset, null);
    }

    // from ContainerInterface

    /**
     * @return bool
     */
    abstract public function has(/*# string */ $id)/*# : bool */;
    /**
     * @return mixed|null
     */
    abstract public function get(/*# string */ $id);

    // from Phossa2\Config\Interfaces\WritableInterface
    /**
     * @return $this
     */
    abstract public function set(/*# string */ $id, $value);
}
