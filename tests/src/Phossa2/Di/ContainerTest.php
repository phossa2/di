<?php
namespace Phossa2\Di;

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
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests Container->get()
     */
    public function testGet()
    {
        // TODO Auto-generated ContainerTest->testGet()
        $this->markTestIncomplete("get test not implemented");

        $this->container->get(/* parameters */);
    }

    /**
     * Tests Container->has()
     */
    public function testHas()
    {
        // TODO Auto-generated ContainerTest->testHas()
        $this->markTestIncomplete("has test not implemented");

        $this->container->has(/* parameters */);
    }

    /**
     * Tests Container->set()
     */
    public function testSet()
    {
        // TODO Auto-generated ContainerTest->testSet()
        $this->markTestIncomplete("set test not implemented");

        $this->container->set(/* parameters */);
    }

    /**
     * Tests Container->one()
     */
    public function testOne()
    {
        // TODO Auto-generated ContainerTest->testOne()
        $this->markTestIncomplete("one test not implemented");

        $this->container->one(/* parameters */);
    }

    /**
     * Tests Container->run()
     */
    public function testRun()
    {
        // TODO Auto-generated ContainerTest->testRun()
        $this->markTestIncomplete("run test not implemented");

        $this->container->run(/* parameters */);
    }
}

