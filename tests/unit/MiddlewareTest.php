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

    public function testCreateMiddlewareMethod()
    {
        $middleware = new Middleware([], []);
        Middleware::create('test', function () {});

        // Test if middleware was set correctly
        $this->assertEquals(
            ['test' => function () {}],
            $this->getPropertyByName('middleware')->getValue($middleware)
        );

        // Test if there is error in case of bad second argument type
        $this->setExpectedException(
            'InvalidArgumentException',
            'Middleware should be callable'
        );
        Middleware::create('test', 1    );
    }

    public function testRetrieveMiddlewareMethod()
    {
        $middleware = new Middleware([], []);
        Middleware::create('test', function () { return 1; });

        // Test if middleware was set correctly
        $this->assertEquals(
            ['test' => function () { return 1; }],
            $this->getPropertyByName('middleware')->getValue($middleware)
        );

        // Test fi retrieved function is correct
        $function = Middleware::retrieve('test');
        $this->assertInstanceOf('Closure', $function);
        $this->assertEquals(1, call_user_func($function));

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