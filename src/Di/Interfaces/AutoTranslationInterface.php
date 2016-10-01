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

/**
 * AutoTranslationInterface
 *
 * Translate 'di.service.storage' to 'storage.di.storage'
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.1.0
 * @since   2.1.0 added
 */
interface AutoTranslationInterface
{
    /**
     * Turn on/off translation
     *
     * @param  bool $flag true or false
     * @return $this
     * @access public
     * @api
     */
    public function translation(/*# bool */ $flag = true);
}
