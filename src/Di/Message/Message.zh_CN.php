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
    Message::DI_PARAMETER_NOTFOUND => '参数 "%s" 没有匹配',
    Message::DI_RESOLVER_NOTFOUND => '搜寻器没有设置',
    Message::DI_FACTORY_NOTFOUND => '实例工厂没有设置',
    Message::DI_CONTAINER_READONLY => '向只读的对象容器写入 "%s"',
    Message::DI_PARAMETER_MISMATCH => '参数 "%s" （函数 "%s"）匹配错误',
    Message::DI_CLASS_UNKNOWN => '发现未知类 "%s"',
    Message::DI_CALLABLE_BAD => '发现错误的可执行代码 "%s"',
];
