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
        $this->object = new Container(new Config(
            new ConfigFileLoader(__DIR__ . '/data')
        ));

        $resolver = $this->invokeMethod('getResolver');
        $this->object = $resolver;
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
     * Call protected/private method of a class.
     *
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeMethod($methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($this->object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->object, $parameters);
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
     * Test get in section
     *
     * @cover Phossa2\Di\Definition\Resolver::getSectionId()
     */
    public function testGetSectionId()
    {
        $this->assertEquals(
            'di.service.test',
            $this->object->getSectionId('test')
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
     * Test autowiring mode
     *
     * @cover Phossa2\Di\Definition\Resolver::auto()
     */
    public function testAuto()
    {
        $this->assertEquals(true, $this->getPrivateProperty('auto'));
        $this->object->auto(false);
        $this->assertEquals(false, $this->getPrivateProperty('auto'));
    }

    /**
     * Test autotranslation
     *
     * @cover Phossa2\Di\Definition\Resolver::translation()
     */
    public function testTranslation()
    {
        $this->assertEquals(true, $this->getPrivateProperty('trans'));
        $this->object->translation(false);
        $this->assertEquals(false, $this->getPrivateProperty('trans'));
    }

    /**
     * Test autoclass
     *
     * @cover Phossa2\Di\Definition\Resolver::autoClassName
     */
    public function testAutoClassName()
    {
        // turn off auto wiring
        $this->object->auto(false);

        // is 'A' defined ?
        $this->assertFalse($this->object->hasService('A'));

        // turn on autowiring
        $this->object->auto(true);

        // is 'A' defined ?
        $this->assertTrue($this->object->hasService('A'));
        $this->assertEquals(
            'A',
            $this->object->getService('A')
        );
    }

    /**
     * Test translation
     *
     * @cover Phossa2\Di\Definition\Resolver::serviceTranslation
     */
    public function testServiceTranslation()
    {
        // turn off translation
        $this->object->translation(false);

        // is 'di.service.test' defined ?
        $this->assertFalse($this->object->hasService('test'));

        // turn on translation
        $this->object->translation(true);

        // 'di.service.delegator' translated to 'delegator.di.delegator'
        $this->assertTrue($this->object->hasService('test'));
    }
}
