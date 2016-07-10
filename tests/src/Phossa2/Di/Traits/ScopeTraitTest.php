<?php
namespace Phossa2\Di;

use Phossa2\Di\Interfaces\ScopeInterface;

/**
 * ScopeTrait test case.
 */
class ScopeTraitTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/Scope.php';
        $this->object = new Scope();
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
     * @covers Phossa2\Di\Traits\ScopeTrait::share()
     */
    public function testShare()
    {
        // shared scope
        $this->object->share(true);
        $this->assertEquals(
            ScopeInterface::SCOPE_SHARED,
            $this->getPrivateProperty('default_scope')
        );

        // single scope
        $this->object->share(false);
        $this->assertEquals(
            ScopeInterface::SCOPE_SINGLE,
            $this->getPrivateProperty('default_scope')
        );
    }

    /**
     * @covers Phossa2\Di\Traits\ScopeTrait::splitId()
     */
    public function testSplitId()
    {
        // id
        $this->assertEquals(
            ['id', ''],
            $this->invokeMethod('splitId', ['id'])
        );

        // id@scope
        $this->assertEquals(
            ['id', 'scope'],
            $this->invokeMethod('splitId', ['id@scope'])
        );

        // id@scope@scope
        $this->assertEquals(
            ['id', 'scope@scope'],
            $this->invokeMethod('splitId', ['id@scope@scope'])
        );
    }

    /**
     * @covers Phossa2\Di\Traits\ScopeTrait::scopedId()
     */
    public function testScopedId()
    {
        // id , scope
        $this->assertEquals(
            'id@scope',
            $this->invokeMethod('scopedId', ['id', 'scope'])
        );

        // id , scope
        $this->assertEquals(
            'id@scope',
            $this->invokeMethod('scopedId', ['id@oldScope', 'scope'])
        );
    }
}
