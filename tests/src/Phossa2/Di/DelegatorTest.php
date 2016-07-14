<?php
namespace Phossa2\Di;

use Phossa2\Config\Config;

/**
 * Delegator test case.
 */
class DelegatorTest extends \PHPUnit_Framework_TestCase
{
    private $object;
    private $data;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/data1.php';

        $this->data = include __DIR__ . '/data2.php';
        $this->object = new Delegator();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->object = null;
        $this->data = null;
        parent::tearDown();
    }

    /**
     * Test get from delegator
     *
     * @cover Phossa2\Di\Delegator::get()
     * @cover Phossa2\Di\Delegator::has()
     * @cover Phossa2\Di\Delegator::set()
     * @cover Phossa2\Di\Delegator::addContainer()
     */
    public function testGet1()
    {
        $container1 = new Container();
        $this->object->addContainer($container1);

        $driver = new \MyCacheDriver();

        // set in delegator, actually in $container1
        $this->object->set('driver', $driver);

        // get from delegator
        $this->assertTrue($driver === $this->object->get('driver'));

        // get from container1
        $this->assertTrue($driver === $container1->get('driver'));

        // container2
        $container2 = new Container(new Config(null, null, $this->data));

        $this->object->addContainer($container2);

        // container2 has 'driver'
        $this->assertTrue($container2->has('driver'));

        // container2's driver is not $driver
        $this->assertTrue($driver !== $container2->get('driver'));

        // add container2 to delegator
        $this->object->addContainer($container2);

        // cache service is the same
        $this->assertTrue($this->object->get('cache') === $container2->get('cache'));

        // driver is different
        $this->assertTrue($this->object->get('driver') !== $container2->get('driver'));

        // even driver in the cache is different
        $this->assertTrue($this->object->get('cache')->getDriver() !== $container2->get('driver'));

        // but same here
        $this->assertTrue($this->object->get('cache')->getDriver() === $container1->get('driver'));
    }
}
