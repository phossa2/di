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
    protected $base_node;

    /**
     * The parameter config object
     *
     * @var    Config
     * @access protected
     */
    protected $config;

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
     * @param  ContainerInterface $container object container
     * @param  Config $config
     * @param  string $nodeName
     * @access public
     */
    public function __construct(
        ContainerInterface $container,
        Config $config,
        /*# string */ $nodeName = 'di'
    ) {
        // object and parameter resolver
        $this->setDelegator(
            (new Delegator())
                // resolving '${#service_id}', non-writable
                ->addRegistry(new ObjectResolver($container))
                // resolving other '${parameter.name}', writable
                ->addRegistry($config->setWritable(true))
        );

        // config
        $this->config = $config;

        // di starting node in $config
        $this->setBaseNode($nodeName);
    }

    /**
     * {@inheritDoc}
     */
    public function get(
        /*# string */ $key,
        /*# string */ $section = ''
    ) {
        return $this->getDelegator()->get($this->generateKey($key, $section));
    }

    /**
     * {@inheritDoc}
     */
    public function has(
        /*# string */ $key,
        /*# string */ $section = ''
    )/*# : bool */ {
        return $this->getDelegator()->has($this->generateKey($key, $section));
    }

    /**
     * {@inheritDoc}
     */
    public function set(
        /*# string */ $key,
        $value,
        /*# string */ $section = ''
    ) {
        $this->getDelegator()->set($this->generateKey($key, $section), $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(&$toResolve)
    {
        $this->config->deReferenceArray($toResolve);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getService(/*# string */ $key)
    {
        return $this->get($key, 'service');
    }

    /**
     * {@inheritDoc}
     */
    public function hasService(/*# string */ $key)/*# : bool */
    {
        if ($this->has($key, 'service') || $this->autoClassName($key)) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function setService(
        /*# string */ $key,
        $definition,
        array $arguments = []
    ) {
        if (!empty($arguments)) {
            $definition = [
                'class' => $definition,
                'args'  => $arguments
            ];
        }

        return $this->set($key, $definition, 'service');
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(/*# string */ $key)
    {
        return $this->get($key, 'mapping');
    }

    /**
     * {@inheritDoc}
     */
    public function hasMapping(/*# string */ $key)/*# : bool */
    {
        return $this->has($key, 'mapping');
    }

    /**
     * {@inheritDoc}
     */
    public function setMapping(/*# string */ $from, $to)
    {
        return $this->set($from, $to, 'mapping');
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
     * {@inheritDoc}
     */
    public function setBaseNode(/*# string */ $nodeName)
    {
        $this->base_node = $nodeName;
        return $this;
    }

    /**
     * Generate key base on section
     *
     * @param  string $key
     * @param  string $section
     * @return string
     * @access protected
     */
    protected function generateKey(
        /*# string */ $key,
        /*# string */ $section = ''
    )/*# : string */ {
        if ('' === $section) {
            return $key;
        } elseif ('' === $key) {
            return $this->base_node . '.' . $section;
        } else {
            return $this->base_node . '.' . $section . '.' . $key;
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
            $this->setService($id, ['class' => $id]);
            return true;
        }
        return false;
    }
}
