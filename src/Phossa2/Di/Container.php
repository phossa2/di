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

namespace Phossa2\Di;

use Phossa2\Config\Config;
use Phossa2\Di\Scope\ScopeTrait;
use Phossa2\Di\Definition\Resolver;
use Phossa2\Di\Scope\ScopeInterface;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Di\Definition\ResolverAwareTrait;
use Phossa2\Di\Interfaces\ContainerInterface;
use Phossa2\Config\Interfaces\ConfigInterface;
use Phossa2\Di\Definition\ResolverAwareInterface;
use Phossa2\Di\Interfaces\ExtendedContainerInterface;

/**
 * Container
 *
 * @package Phossa2\Di
 * @author  Hong Zhang <phossa@126.com>
 * @see     ObjectAbstract
 * @see     ContainerInterface
 * @see     DefinitionInterface
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Container extends ObjectAbstract implements ContainerInterface, ResolverAwareInterface, ScopeInterface, ExtendedContainerInterface
{
    use ResolverAwareTrait, ScopeTrait;

    /**
     * services pool
     *
     * @var    object[]
     * @access protected
     */
    protected $pool = [];

    /**
     * id cache for loop detection
     *
     * @var    array
     * @access protected
     */
    protected $loop = [];

    /**
     * Constructor
     *
     * @param  Config $config
     * @access public
     */
    public function __construct(
        ConfigInterface $config = null
    ) {
        // set definition/reference resolver
        $this->setResolver(new Resolver($this, $config));
    }

    /**
     * {@inheritDoc}.
     */
    public function get($id)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        if (is_string($id)) {
            // split id and scope
            list($id, $scope) = $this->splitId($id);

            // try definition
            return $this->getResolver()->hasServiceDefinition($id);
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function one(/*# string */ $id, array $arguments = [])
    {
        return $this->get($id, $arguments, ScopeInterface::SCOPE_SINGLE);
    }

    /**
     * {@inheritDoc}
     */
    public function run($callable, array $arguments = [])
    {
        // dereference arguments
        if (!empty($arguments)) {
            $arguments = $this->resolve($arguments);
        }

        return call_user_func_array(
            $this->resolve($callable),
            $arguments
        );
    }

    /**
     * {@inheritDoc}
     */
    public function batch(array $methods)
    {
        foreach ($methods as $mthd) {
            $this->run(
                $mthd['func'],
                isset($mthd['args']) ? $mthd['args'] : []
            );
        }
    }
}
