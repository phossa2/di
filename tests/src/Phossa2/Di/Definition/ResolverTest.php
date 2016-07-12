<?php
namespace Phossa2\Di\Definition;

use Phossa2\Di\Container;
use Phossa2\Config\Config;
use Phossa2\Config\Loader\ConfigFileLoader;
use Phossa2\Config\Delegator;

/**
 * Resolver test case.
 */
class ResolverTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        require_once __DIR__ .'/testData.php';

        parent::setUp();
        $container = new Container(new Config(
            new ConfigFileLoader(__DIR__ . '/data')
        ));
        $this->object = $container->getResolver();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->object = null;
        parent::tearDown();
    }

    /**
     * getPrivateProperty
     *
     * @param  string $propertyName
     * @return the property
     */
    public function getPrivateProperty($propertyName) {
        $reflector = new \ReflectionClass($this->object);
        $property  = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($this->object);
    }

    /**
     * Test resolve definition
     *
     * @cover Phossa2\Di\Definition\Resolver::resolve()
     */
    public function testResolve()
    {
        $toResolve = ['test ${di.service.delegator2.class}', '${#A}'];
        $this->object->resolve($toResolve);
        $this->assertRegExp('/Delegator/', $toResolve[0]);
        $this->assertTrue($toResolve[1] instanceof \A);
    }

    /**
     * Test get
     *
     * @cover Phossa2\Di\Definition\Resolver::get()
     */
    public function testGet()
    {
        // full patch
        $this->assertEquals(
            Delegator::getClassName(),
            $this->object->get('di.service.delegator.class')
        );

        // return NULL if not found
        $this->assertEquals(
            null,
            $this->object->get('no.such.node')
        );

        // result is resolved
        $this->assertEquals(
            'Phossa2\\Config\\Delegator',
            $this->object->get('di.service.delegator2.class')
        );
    }

    /**
     * Test get in section
     *
     * @cover Phossa2\Di\Definition\Resolver::getInSection()
     */
    public function testGetInSection()
    {
        $this->assertEquals(
            Delegator::getClassName(),
            $this->object->getInSection('delegator.class', 'service')
        );
    }

    /**
     * Test has
     *
     * @cover Phossa2\Di\Definition\Resolver::has()
     */
    public function testHas()
    {
        // full patch
        $this->assertTrue(
            $this->object->has('di.service.delegator.class')
        );

        // return FALSE if not found
        $this->assertFalse(
            $this->object->has('no.such.node')
        );
    }

    /**
     * Test has in section
     *
     * @cover Phossa2\Di\Definition\Resolver::hasInSection()
     */
    public function testHasInSection()
    {
        $this->assertTrue(
            $this->object->hasInSection('delegator.class', 'service')
        );
    }

    /**
     * Test set
     *
     * @cover Phossa2\Di\Definition\Resolver::set()
     */
    public function testSet()
    {
        // not found
        $this->assertFalse(
            $this->object->has('no.such.node')
        );

        // add it
        $this->object->set('no.such.node', '12');

        // found now
        $this->assertEquals(
            '12',
            $this->object->get('no.such.node')
        );
    }

    /**
     * Test set in section
     *
     * @cover Phossa2\Di\Definition\Resolver::setInSection()
     */
    public function testSetInSection()
    {
        $this->assertFalse(
            $this->object->hasInSection('no.such.node', 'mapping')
        );
        $this->object->setInSection('no.such.node', 'mapping', '10');
        $this->assertEquals(
            '10',
            $this->object->getInSection('no.such.node', 'mapping')
        );
    }

    /**
     * Test get service definition
     *
     * @cover Phossa2\Di\Definition\Resolver::getService()
     */
    public function testGetService()
    {
        // full patch
        $this->assertEquals(
            Delegator::getClassName(),
            $this->object->getService('delegator.class')
        );

        // return NULL if not found
        $this->assertEquals(
            null,
            $this->object->getService('no.such.node')
        );
    }

    /**
     * Test has service definition
     *
     * @cover Phossa2\Di\Definition\Resolver::hasService()
     */
    public function testHasService()
    {
        // full patch
        $this->assertTrue(
            $this->object->hasService('delegator.class')
        );

        // return FALSE if not found
        $this->assertFalse(
            $this->object->hasService('no.such.node')
        );
    }

    /**
     * Test set service definition
     *
     * @cover Phossa2\Di\Definition\Resolver::setService()
     */
    public function testSetService()
    {
        // not found
        $this->assertFalse(
            $this->object->hasService('no.such.node')
        );

        // add it
        $this->object->setService('no.such.node', '12');

        // found now
        $this->assertEquals(
            '12',
            $this->object->getService('no.such.node')
        );
    }

    /**
     * Test get mapping definition
     *
     * @cover Phossa2\Di\Definition\Resolver::getMapping()
     */
    public function testGetMapping()
    {
        // full patch
        $this->assertEquals(
            'AClass',
            $this->object->getMapping('AnInterface')
        );

        // return NULL if not found
        $this->assertEquals(
            null,
            $this->object->getMapping('no.such.node')
        );
    }

    /**
     * Test has mapping definition
     *
     * @cover Phossa2\Di\Definition\Resolver::hasMapping()
     */
    public function testHasMapping()
    {
        // full patch
        $this->assertTrue(
            $this->object->hasMapping('AnInterface')
        );

        // return FALSE if not found
        $this->assertFalse(
            $this->object->hasMapping('no.such.node')
        );
    }

    /**
     * Test set mapping definition
     *
     * @cover Phossa2\Di\Definition\Resolver::setMapping()
     */
    public function testSetMapping()
    {
        // not found
        $this->assertFalse(
            $this->object->hasMapping('no.such.node')
        );

        // add it
        $this->object->setMapping('no.such.node', 'wow');

        // found now
        $this->assertEquals(
            'wow',
            $this->object->getMapping('no.such.node')
        );
    }

    /**
     * Test autowiring mode
     *
     * @cover Phossa2\Di\Definition\Resolver::autoWiring()
     */
    public function testAutoWiring()
    {
        $this->assertEquals(true, $this->getPrivateProperty('auto'));
        $this->object->autoWiring(false);
        $this->assertEquals(false, $this->getPrivateProperty('auto'));
    }

    /**
     * Test autoclass
     *
     * @cover Phossa2\Di\Definition\Resolver::autoClassName
     */
    public function testAutoClassName()
    {
        // turn off autowiring
        $this->object->autoWiring(false);

        // is 'A' defined ?
        $this->assertFalse($this->object->hasService('A'));

        // turn on autowiring
        $this->object->autoWiring(true);

        // is 'A' defined ?
        $this->assertTrue($this->object->hasService('A'));
        $this->assertEquals(
            ['class'=> 'A'],
            $this->object->getService('A')
        );
    }
}
