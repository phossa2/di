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

namespace Phossa2\Di\Definition;

use Phossa2\Config\Config;
use Phossa2\Config\Delegator;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Di\Interfaces\ContainerInterface;
use Phossa2\Config\Interfaces\ConfigInterface;
use Phossa2\Shared\Reference\DelegatorAwareTrait;
use Phossa2\Shared\Reference\DelegatorAwareInterface;

/**
 * Resolver
 *
 * One implementation of ResolverInterface using
 *
 * - Phossa2\Config\Delegator
 * - Phossa2\Config\Config
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ObjectAbstract
 * @see     ResolverInterface
 * @see     DelegatorAwareInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Resolver extends ObjectAbstract implements ResolverInterface, DelegatorAwareInterface
{
    use DelegatorAwareTrait;

    /**
     * Container definition starting node
     *
     * @var    string
     * @access protected
     */
    protected $node_base;

    /**
     * Autowiring ON or OFF
     *
     * @var    bool
     * @access protected
     */
    protected $auto = true;

    /**
     * Create resolver with object resolving and reference resolving capability
     *
     * @param  ContainerInterface $container
     * @param  ConfigInterface $config
     * @param  string $nodeName
     * @access public
     */
    public function __construct(
        ContainerInterface $container,
        ConfigInterface $config,
        /*# string */ $nodeName = 'di'
    ) {
        // object and parameter resolver
        $this->setDelegator(
            (new Delegator())
                // resolving '${#service_id}' object
                ->addRegistry(new ObjectResolver($container))
                // resolving other '${parameter.name}' etc.
                ->addRegistry($config)
        );

        // di starting node in $config
        $this->setBaseNode($nodeName);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinition(
        /*# string */ $key,
        /*# string */ $section = ''
    ) {
        return $this->getDelegator()->get($this->getKey($key, $section));
    }

    /**
     * {@inheritDoc}
     */
    public function hasDefinition(
        /*# string */ $key,
        /*# string */ $section = ''
    )/*# : bool */ {
        return $this->getDelegator()->has($this->getKey($key, $section));
    }

    /**
     * {@inheritDoc}
     */
    public function setDefinition(
        /*# string */ $key,
        $value,
        /*# string */ $section = ''
    ) {
        $this->getDelegator()->set($this->getKey($key, $section), $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceDefinition(/*# string */ $serviceId)
    {
        return $this->getDefinition($serviceId, 'service');
    }

    /**
     * {@inheritDoc}
     */
    public function hasServiceDefinition(/*# string */ $serviceId)/*# : bool */
    {
        if ($this->hasDefinition($serviceId, 'service') ||
            $this->autoClassName($serviceId)
        ) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function setServiceDefinition(
        /*# string */ $serviceId,
        $definition,
        array $arguments = []
    ) {
        if (!empty($arguments)) {
            $definition = [
                'class' => $definition,
                'args'  => $arguments
            ];
        }

        return $this->setDefinition($serviceId, $definition, 'service');
    }

    /**
     * {@inheritDoc}
     */
    public function getMappingDefinition(/*# string */ $key)
    {
        return $this->getDefinition($key, 'mapping');
    }

    /**
     * {@inheritDoc}
     */
    public function hasMappingDefinition(/*# string */ $key)/*# : bool */
    {
        return $this->hasDefinition($key, 'mapping');
    }

    /**
     * {@inheritDoc}
     */
    public function setMappingDefinition(/*# string */ $from, $to)
    {
        return $this->setDefinition($from, $to, 'mapping');
    }

    /**
     * {@inheritDoc}
     */
    public function autoWiring(/*# bool */ $on = true)
    {
        $this->auto = $on;
        return $this;
    }

    /**
     * Set the container definition starting node in $config object
     *
     * @param  string $nodeName
     * @return $this
     * @access protected
     */
    protected function setBaseNode(/*# string */ $nodeName)
    {
        $this->node_base = $nodeName;
        return $this;
    }

    /**
     * Get key in $config tree
     *
     * @param  string $key
     * @param  string $section
     * @return string
     * @access protected
     */
    protected function getKey(
        /*# string */ $key,
        /*# string */ $section = ''
    )/*# : string */ {
        if ('' === $section) {
            return $key;
        } else {
            return $this->node_base . '.' . $section . '.' . $key;
        }
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
            $this->setServiceDefinition($id, ['class' => $id]);
            return true;
        }
        return false;
    }
}
