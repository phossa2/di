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
        $this->object->getResolver()->autoWiring(false);

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
}
