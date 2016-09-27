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
     * Test get with autowiring
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
     * Test get() with set() and autowiring off
     *
     * @cover Phossa2\Di\Container::get()
     * @cover Phossa2\Di\Container::has()
     * @cover Phossa2\Di\Container::set()
     * @cover Phossa2\Di\Container::auto()
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
     * Test get() with config
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
    public function testAuto1()
    {
        $this->object->auto(false);
        $this->assertFalse($this->object->has('MyCache'));
        $this->assertTrue($this->object->get('MyCache') instanceof \MyCache);
    }

    /**
     * Test turn on/off autowiring
     *
     * @cover Phossa2\Di\Container::auto()
     * @cover Phossa2\Di\Container::isAuto()
     */
    public function testAuto2()
    {
        $this->assertTrue($this->object->isAuto());
        $this->object->auto(false);
        $this->assertFalse($this->object->isAuto());
        $this->object->auto(true);
        $this->assertTrue($this->object->isAuto());
    }

    /**
     * Test mapping to a classname string
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
     * Test one()
     *
     * @cover Phossa2\Di\Container::one()
     */
    public function testOne1()
    {
        $cache1 = $this->object->get('MyCache');
        $cache2 = $this->object->get('MyCache');
        $cache3 = $this->object->one('MyCache');
        $cache4 = $this->object->one('MyCache');

        $this->assertTrue($cache1 === $cache2);
        $this->assertTrue($cache1 !== $cache3);
        $this->assertTrue($cache3 !== $cache4);

        // with scope
        $cache5 = $this->object->get('MyCache@myscope');
        $cache6 = $this->object->get('MyCache@myscope');
        $cache7 = $this->object->one('MyCache@myscope');
        $cache8 = $this->object->one('MyCache@myscope');

        $this->assertTrue($cache1 !== $cache5);
        $this->assertTrue($cache5 === $cache6);
        $this->assertTrue($cache5 !== $cache7);
        $this->assertTrue($cache7 !== $cache8);
    }

    /**
     * Test run()
     *
     * @cover Phossa2\Di\Container::run()
     * @cover Phossa2\Di\Container::param()
     */
    public function testRun1()
    {
        $this->expectOutputString('test_wow_xx_');

        // php function
        $this->object->run('printf', ['test_']);

        // callable
        $this->object->run(function() { echo 'wow_'; });

        // pseudo callable
        $this->object->param('method', 'echoIt');
        $this->object->param('string', 'xx_');
        $this->object->run(['${#MyCache}', '${method}'], ['${string}']);
    }

    /**
     * Test alias(), always pointing to the same instance
     *
     * @cover Phossa2\Di\Container::alias()
     */
    public function testAlias()
    {
        // alias
        $this->object->alias('cache1', '${#MyCache}');

        // shared instance
        $cache = $this->object->get('MyCache');

        $this->assertTrue($this->object->get('cache1') === $cache);

        // alias with scope, still same
        $this->object->alias('cache2@myscope', '${#MyCache}');
        $this->assertTrue($this->object->get('cache2') === $cache);

        // can NOT alias with same name (even scope is different)
        $this->assertFalse($this->object->alias('cache2', '${#MyCache}'));
        $this->assertTrue($this->object->get('cache2') === $cache);

        // scoped instance
        $cacheScoped = $this->object->get('MyCache@myscope');

        $this->assertTrue($cache !== $cacheScoped);

        // alias scoped
        $this->object->alias('cache3', '${#MyCache@myscope}');
        $this->assertTrue($this->object->get('cache3') === $cacheScoped);
    }

    /**
     * Test share
     *
     * @cover Phossa2\Di\Container::share()
     */
    public function testShare()
    {
        $cache1 = $this->object->get('MyCache');
        $cache2 = $this->object->get('MyCache');

        $this->assertTrue($cache1 === $cache2);

        $cache3 = $this->object->one('MyCache');

        // different
        $this->assertTrue($cache1 !== $cache3);

        // but driver is same
        $this->assertTrue($cache1->getDriver() === $cache3->getDriver());

        $this->object->share(false);

        $cache4 = $this->object->get('MyCache');
        $cache5 = $this->object->get('MyCache');

        $this->assertTrue($cache4 !== $cache5);
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
        $this->expectOutputString('a_b_b_');

        // set up common methods
        $this->object->param(
            'di.common', [
                [
                    function($obj) { return $obj instanceof \MyCacheDriver; },
                    function() { echo 'a_'; }
                ],
                [
                    function($obj) { return true; },
                    function() { echo 'b_'; }
                ],
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

    /**
     * Test setWritable() & isWritable()
     *
     * @cover Phossa2\Di\Container::setWritable()
     * @cover Phossa2\Di\Container::isWritable()
     */
    public function testIsWritable()
    {
        $container = new Container();

        // default is writable
        $this->assertTrue($container->isWritable());

        // set readonly
        $this->assertTrue($container->setWritable(false));

        // now not writable
        $this->assertFalse($container->isWritable());
    }
}
