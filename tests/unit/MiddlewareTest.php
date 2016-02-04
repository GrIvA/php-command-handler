<?php

use CommandHandler\Middleware;

class MiddlewareTest extends \Codeception\TestCase\Test
{

    const MIDDLEWARE_CLASS = 'CommandHandler\Middleware';

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testSetMiddleware()
    {
        $middleware = new Middleware([], []);
        $middleware->test = function () {
            return 1;
        };
        $stored_middleware = $this->getPropertyByName('middleware')->getValue($middleware);
        // Test setting of the correct parameter types
        $this->assertEquals(
            ['test' => function () {}],
            $stored_middleware
        );
        $this->assertEquals(call_user_func($stored_middleware['test']), 1);

        // Test invalid second parameter type passing
        $this->setExpectedException(
            'InvalidArgumentException',
            'Middleware should be callable'
        );
        $middleware->test2 = 2;
    }

    public function testGetMiddleware()
    {
        $middleware = new Middleware([], []);
        $middleware->test = function () {
            return 1;
        };
        $stored_middleware = $this->getPropertyByName('middleware')->getValue($middleware);
        // Test getting middleware after set
        $this->assertEquals(
            $stored_middleware['test'],
            $middleware->test
        );
        $this->assertEquals(
            call_user_func($middleware->test),
            call_user_func($stored_middleware['test'])
        );
    }

    public function testInstanceCreation()
    {
        $middleware = new Middleware(
            [
                'before' => function () {}
            ],
            [
                'after' => function () {}
            ]
        );
        // Test if until new instance creation all required middle was set correctly
        $this->assertEquals(
            ['before' => function () {}],
            $this->getPropertyByName('before')->getValue($middleware)
        );
        $this->assertEquals(
            ['after' => function () {}],
            $this->getPropertyByName('after')->getValue($middleware)
        );
    }

    public function testAddMethod()
    {
        $middleware = new Middleware([], []);
        $propertyBefore = $this->getPropertyByName('before');
        $propertyAfter = $this->getPropertyByName('after');

        // Test if by default values are empty
        $this->assertEmpty($propertyBefore->getValue($middleware));
        $this->assertEmpty($propertyAfter->getValue($middleware));

        // Test setting of only before middleware list
        $middleware->add([function () {}], []);
        $this->assertNotEmpty($propertyBefore->getValue($middleware));
        $this->assertEmpty($propertyAfter->getValue($middleware));

        // Test setting of only after middleware list
        $middleware->add([], [function () {}]);
        $this->assertNotEmpty($propertyBefore->getValue($middleware));
        $this->assertNotEmpty($propertyAfter->getValue($middleware));
    }

    private static function getPropertyByName($property)
    {
        $class = new ReflectionClass(self::MIDDLEWARE_CLASS);
        $property = $class->getProperty($property);
        $property->setAccessible(true);

        return $property;
    }

}