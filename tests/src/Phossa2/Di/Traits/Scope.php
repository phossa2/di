<?php
/**
 * Phossa Project
 *
 * PHP version 5.4
 *
 * @category  Library
 * @package   Phossa2\PKG
 * @copyright Copyright (c) 2016 phossa.com
 * @license   http://mit-license.org/ MIT License
 * @link      http://www.phossa.com/
 */
/*# declare(strict_types=1); */

namespace Phossa2\Di;

use Phossa2\Di\Interfaces\ScopeInterface;
use Phossa2\Di\Traits\ScopeTrait;

/**
 * Sample Scope class
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     PKG
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Scope implements ScopeInterface {
    use ScopeTrait;

    protected function getResolver()/*# : ResolverInterface */
    {
    }
}
