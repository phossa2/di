<?php
namespace Phossa2\Di;

use Phossa2\Config\Config;

/**
 * Container test case.
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->object = new Container();

        require_once __DIR__ . '/data1.php';

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
     * Test get (use autowiring)
     *
     * @cover Phossa2\Di\Container::get()
     * @cover Phossa2\Di\Container::has()
     */
    public function testGet1()
    {
        $this->assertTrue($this->object->has('MyCache'));
        $this->assertTrue($this->object->get('MyCache') instanceof \MyCache);
    }

    /**
     * Test get (use set())
     *
     * @cover Phossa2\Di\Container::get()
     * @cover Phossa2\Di\Container::has()
     */
    public function testGet2()
    {
        // turnoff autowiring
        $this->object->auto(false);

        // add service 'cache'
        $this->object->set('cache', [
            'class' => 'MyCache', // classname
            'args'  => ['${#driver}'] // constructor arguments
        ]);

        // add service 'driver' with a callback
        $this->object->set('driver', function() {
            return new \MyCacheDriver();
        });

        $this->assertTrue($this->object->has('cache'));
        $this->assertTrue($this->object->get('cache') instanceof \MyCache);
    }

    /**
     * Test get (use config)
     *
     * @cover Phossa2\Di\Container::get()
     * @cover Phossa2\Di\Container::has()
     */
    public function testGet3()
    {
        $configData = include __DIR__ . '/data2.php';

        // create $config
        $config = new Config(null, null, $configData);

        // instantiate container
        $container = new $config['di.class']($config);

        $this->assertTrue($container->has('cache'));
        $this->assertTrue($container->get('cache') instanceof \MyCache);
    }

    /**
     * Test turn off autowiring
     *
     * @cover Phossa2\Di\Container::auto()
     * @expectedException Phossa2\Di\Exception\NotFoundException
     * @expectedExceptionCode Phossa2\Di\Message\Message::DI_SERVICE_NOTFOUND
     */
    public function testAuto()
    {
        $this->object->auto(false);
        $this->assertFalse($this->object->has('MyCache'));
        $this->assertTrue($this->object->get('MyCache') instanceof \MyCache);
    }

    /**
     * Test mapping to a classname
     *
     * @cover Phossa2\Di\Container::set()
     */
    public function testSet1()
    {
        $this->object->set('DriverInterface', 'MyCacheDriver');
        $this->assertTrue($this->object->get('YourCache') instanceof \YourCache);
    }

    /**
     * Test mapping to a callback returns an object
     *
     * @cover Phossa2\Di\Container::set()
     */
    public function testSet2()
    {
        $this->object->set('DriverInterface', function() {
            return new \MyCacheDriver();
        });
        $this->assertTrue($this->object->get('YourCache') instanceof \YourCache);
    }

    /**
     * Test mapping to an object directly
     *
     * @cover Phossa2\Di\Container::set()
     */
    public function testSet3()
    {
        $this->object->set('DriverInterface', new \MyCacheDriver());
        $this->assertTrue($this->object->get('YourCache') instanceof \YourCache);
    }

    /**
     * Test mapping to service reference
     *
     * @cover Phossa2\Di\Container::set()
     */
    public function testSet4()
    {
        // set a service
        $this->object->set('thedriver', new \MyCacheDriver());

        // map to a service reference
        $this->object->set('DriverInterface', '${#thedriver}');

        $this->assertTrue($this->object->get('YourCache') instanceof \YourCache);
    }

    /**
     * Test mapping to paramter reference
     *
     * @cover Phossa2\Di\Container::set()
     */
    public function testSet5()
    {
        // setup a parameter
        $this->object->param('the.driver', 'MyCacheDriver');

        // map to a parameter reference
        $this->object->set('DriverInterface', '${the.driver}');

        $this->assertTrue($this->object->get('YourCache') instanceof \YourCache);
    }

    /**
     * Test failed mapping
     *
     * @cover Phossa2\Di\Container::map()
     * @expectedException Phossa2\Di\Exception\LogicException
     * @expectedExceptionCode Phossa2\Di\Message\Message::DI_CLASS_UNKNOWN
     */
    public function testSet6()
    {
        $this->assertTrue($this->object->get('YourCache') instanceof \YourCache);
    }

    /**
     * Test running methods for instance
     *
     * @cover Phossa2\Di\Container::get()
     */
    public function testGet4()
    {
        $this->expectOutputString('a_runMethod1_driverMethod_b_c_');

        $this->object->set('newcache', [
            'class'   => 'MyCache',
            'methods' => [
                ['printf', ['a_']], // a function
                ['runMethod1'], // newcache's method
                [['${#MyCacheDriver}', 'driverMethod'], []], // another service method
                function() { echo "b_"; }, // callable
                [function($s) { echo $s; }, [ "c_" ]], // callable with args
            ]
        ]);

        // only run once !
        $this->object->get('newcache');
        $this->object->get('newcache');
    }

    /**
     * Test running common methods for instance
     *
     * @cover Phossa2\Di\Container::get()
     */
    public function testGet5()
    {
        $this->expectOutputString('');

        // set up common methods
        $this->object->param(
            'di.common', [
            ]
        );

        // only run once !
        $this->object->get('MyCache');
    }

    /**
     * Test get in shared scope
     *
     * @cover Phossa2\Di\Container::get()
     */
    public function testGet6()
    {
        $a1 = $this->object->get('A');
        $a2 = $this->object->get('A');
        $a3 = $this->object->one('A');
        $a4 = $this->object->get('A@scope');

        $this->assertTrue($a1 === $a2);
        $this->assertTrue($a1 !== $a3);
        $this->assertTrue($a1 !== $a4);

        // same C for different A
        $this->assertTrue($a1->getC() === $a3->getC());
    }

    /**
     * Test shared in service scope
     *
     * @cover Phossa2\Di\Container::get()
     */
    public function testGet7()
    {
        // define C shared under A
        $this->object->set('C', ['class' => 'C', 'scope' => '#A']);

        $a1 = $this->object->one('A');
        $a2 = $this->object->one('A');

        // different As
        $this->assertTrue($a1 !== $a2);

        // different C for different A
        $this->assertTrue($a1->getC() !== $a2->getC());

        // same C for same A
        $this->assertTrue($a1->getC() === $a1->getB()->getC());
    }

    /**
     * Test array access
     *
     * @cover Phossa2\Di\Container::get()
     */
    public function testGet8()
    {
        $container = new Container();
        $delegator = new Delegator();
        $delegator->addContainer($container);

        $this->assertTrue(isset($container['A']));
        $this->assertTrue($delegator['A'] === $container['A']);
    }
}
