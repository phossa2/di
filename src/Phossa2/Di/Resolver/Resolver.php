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

/**
 * Resolver
 *
 * - Resolver is a config delegator with
 *
 *   - '#service_id' type of objects lookup
 *   - parameter config lookup.
 *
 * - Resolver implements ResolverInterface for easy access to different sections
 *   of the parameter config.
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     \Phossa2\Config\Delegator
 * @see     ResolverInterface
 * @see     AutoWiringInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Resolver extends ConfigDelegator implements ResolverInterface, AutoWiringInterface
{
    /**
     * The outer master
     *
     * @var    Container
     * @access protected
     */
    protected $master;

    /**
     * The object resolver
     *
     * @var    ObjectResolver
     * @access protected
     */
    protected $object_resolver;

    /**
     * The parameter resolver
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
     * Autowiring: automatically resolve classname if it is a defined class
     *
     * @var    bool
     * @access protected
     */
    protected $auto = true;

    /**
     * @param  Container $master the master
     * @param  Config $config used for parameter resolving
     * @param  string $nodeName
     * @access public
     */
    public function __construct(
        Container $master,
        Config $config,
        /*# string */ $nodeName
    ) {
        // set config and make it/self writable
        $this->config_resolver = $config;

        // set base node
        $this->base_node = $nodeName;

        // set object resolver
        $this->master = $master;
        $this->object_resolver = new ObjectResolver();
        $this->setObjectResolver();

        // set up lookup pool
        $this->addRegistry($this->object_resolver)->addRegistry($config);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(&$toResolve)
    {
        $this->config_resolver->deReferenceArray($toResolve);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setObjectResolver()
    {
        // either master container or master's container delegator
        if ($this->master->hasDelegator()) {
            $container = $this->master->getDelegator();
        } else {
            $container = $this->master;
        }
        $this->object_resolver->setContainer($container);
    }

    /**
     * {@inheritDoc}
     */
    public function getInSection(/*# string */ $id, /*# string */ $section)
    {
        return $this->get($this->getSectionId($id, $section));
    }

    /**
     * {@inheritDoc}
     */
    public function hasInSection(
        /*# string */ $id,
        /*# string */ $section
    )/*# : bool */ {
        return $this->has($this->getSectionId($id, $section));
    }

    /**
     * {@inheritDoc}
     */
    public function setInSection(
        /*# string */ $id,
        /*# string */ $section,
        $value
    ) {
        $this->set($this->getSectionId($id, $section), $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getService(/*# string */ $id = '')
    {
        if ($this->hasInSection($id, 'service')) {
            return $this->getInSection($id, 'service');

        } else if ($this->autoClassName($id)) {
            return ['class' => $id];

        } else {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function hasService(/*# string */ $id = '')/*# : bool */
    {
        // with autoWiring supported
        if ($this->hasInSection($id, 'service') || $this->autoClassName($id)) {
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
    ) {
        if (!empty($args)) {
            $definition = [
                'class' => $definition,
                'args'  => $args
            ];
        }
        return $this->setInSection($id, 'service', $definition);
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(/*# string */ $id = '')
    {
        return $this->getInSection($id, 'mapping');
    }

    /**
     * {@inheritDoc}
     */
    public function hasMapping(/*# string */ $id = '')/*# : bool */
    {
        return $this->hasInSection($id, 'mapping');
    }

    /**
     * {@inheritDoc}
     */
    public function setMapping(/*# string */ $from, $to)
    {
        return $this->setInSection($from, 'mapping', $to);
    }

    /**
     * {@inheritDoc}
     */
    public function autoWiring(/*# bool */ $on = true)
    {
        $this->auto = (bool) $on;
        return $this;
    }

    /**
     * Generate new id base on base and section
     *
     * @param  string $id
     * @param  string $section
     * @return string
     * @access protected
     */
    protected function getSectionId(
        /*# string */ $id,
        /*# string */ $section
    )/*# : string */ {
        $sec = $this->base_node . '.' . $section;
        return '' == $id ? $sec : ($sec . '.' . $id);
    }

    /**
     * If autowiring is on, and $id is a existing classname, return true
     *
     * @param  string $id
     * @return bool
     * @access protected
     */
    protected function autoClassName(/*# string */ $id)/*# : bool */
    {
        if ($this->auto && class_exists($id)) {
            return true;
        }
        return false;
    }
}
