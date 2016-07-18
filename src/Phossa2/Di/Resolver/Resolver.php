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

namespace Phossa2\Di\Resolver;

use Phossa2\Di\Container;
use Phossa2\Config\Config;
use Phossa2\Di\Interfaces\ResolverInterface;
use Phossa2\Di\Interfaces\AutoWiringInterface;
use Phossa2\Config\Interfaces\ConfigInterface;
use Phossa2\Config\Delegator as ConfigDelegator;
use Phossa2\Shared\Reference\ReferenceInterface;
use Phossa2\Di\Interfaces\ReferenceResolveInterface;

/**
 * Resolver
 *
 * A config delegator for resolving service or parameter references
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     \Phossa2\Config\Delegator
 * @see     ResolverInterface
 * @see     AutoWiringInterface
 * @see     ReferenceResolveInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Resolver extends ConfigDelegator implements ResolverInterface, AutoWiringInterface, ReferenceResolveInterface
{
    /**
     * The config for object resolving
     *
     * @var    ObjectResolver
     * @access protected
     */
    protected $object_resolver;

    /**
     * The config for parameter resolver
     *
     * @var    ConfigInterface
     * @access protected
     */
    protected $config_resolver;

    /**
     * Container related definition starting node at $config
     *
     * @var    string
     * @access protected
     */
    protected $base_node;

    /**
     * For autowiring
     *
     * @var    bool
     * @access protected
     */
    protected $auto = true;

    /**
     * @param  Container $container
     * @param  ConfigInterface $config inject config for parameter resolving
     * @param  string $nodeName
     * @access public
     */
    public function __construct(
        Container $container,
        ConfigInterface $config,
        /*# string */ $nodeName
    ) {
        // set parameter resolver
        $this->config_resolver = $config;
        $this->base_node = $nodeName;

        // set object resolver
        $this->object_resolver = new ObjectResolver($container);

        // delegator
        $this->addConfig($this->object_resolver);
        $this->addConfig($this->config_resolver);
    }

    /**
     * Resolving use the parameter resolver
     *
     * {@inheritDoc}
     */
    public function resolve(&$toResolve)
    {
        if ($this->config_resolver instanceof ReferenceInterface) {
            $this->config_resolver->deReferenceArray($toResolve);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getService(/*# string */ $id = '')
    {
        if ($this->hasService($id)) {
            return $this->get($this->getSectionId($id));
        } else {
            return null;
        }
    }

    /**
     * Autowiring support added
     *
     * {@inheritDoc}
     */
    public function hasService(/*# string */ $id = '')/*# : bool */
    {
        $sid = $this->getSectionId($id);
        if ($this->has($sid) || $this->autoClassName($id)) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function setService(
        /*# string */ $id,
        $definition,
        array $args = []
    )/*# : bool */ {
        if (!empty($args)) {
            $definition = [
                'class' => $definition,
                'args'  => $args
            ];
        }
        return $this->set($this->getSectionId($id), $definition);
    }

    /**
     * {@inheritDoc}
     */
    public function getSectionId(
        /*# string */ $id,
        /*# string */ $section = 'service'
    )/*# : string */ {
        $sec = $this->base_node . '.' . $section;
        return '' == $id ? $sec : ($sec . '.' . $id);
    }

    /**
     * {@inheritDoc}
     */
    public function auto(/*# bool */ $flag = true)
    {
        $this->auto = (bool) $flag;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuto()/*# : bool */
    {
        return $this->auto;
    }

    /**
     * Returns true if
     *
     * 1) autowiring is true
     * 2) $id is a existing classname
     * 3) resolver $this is writable
     *
     * @param  string $id
     * @return bool
     * @access protected
     */
    protected function autoClassName(/*# string */ $id)/*# : bool */
    {
        if ($this->auto && class_exists($id) && $this->isWritable()) {
            return $this->setService($id, $id);
        }
        return false;
    }
}
