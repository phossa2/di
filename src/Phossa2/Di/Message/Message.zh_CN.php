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

use Phossa2\Di\Message\Message;

/*
 * Provide zh_CN translation
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
return [
    Message::DI_CONTAINER_NOTFOUND => '对象容器没有设置',
    Message::DI_SERVICE_NOTFOUND => '服务实例  "%s" 没有找到',
    Message::DI_LOOP_DETECTED => '对象容器发现 "%s" 死循环',
];
