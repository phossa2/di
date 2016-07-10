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
}
