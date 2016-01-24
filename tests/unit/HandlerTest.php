<?php

use CommandHandler\Handler;

class HandlerTest extends \Codeception\TestCase\Test
{

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

    public function testMiddlewareMethod()
    {
        $handler = new Handler();
        $middleware = self::getPropertyByName('middleware');

        $handler->middleware(
            [],
            function () use ($middleware, $handler) {
                $current_middleware = $middleware->getValue($handler);
                // Test setting of the middleware instance
                $this->assertInstanceOf(
                    'CommandHandler\Middleware',
                    $middleware->getValue($handler)
                );
                $handler->middleware(
                    [],
                    function () use ($current_middleware, $handler, $middleware) {
                        // Test inner middleware
                        $this->assertNotSame(
                            $current_middleware,
                            $middleware->getValue($handler)
                        );
                    },
                    []
                );
            },
            []
        );
    }

    public function testGroupMethod()
    {
        $handler = new Handler();
        $requests = self::getPropertyByName('requests');

        $handler->group('group_test/', function () use ($handler)
        {
            $handler->add('first', function () {});
            $handler->add('second', function () {});

            $handler->group('inner_group/', function () use ($handler)
            {
                $handler->add('third', function () {});
            });

        });

        // Test setting of the groups and inner groups with commands
        $this->assertEquals(
            [
                'group_test/first',
                'group_test/second',
                'group_test/inner_group/third',
            ],
            array_keys($requests->getValue($handler))
        );

        $this->setExpectedException(
            'InvalidArgumentException',
            'Group callback should be callable'
        );
        $handler->group('/', 1);
    }

    public function testAddMethod()
    {
        $handler = new Handler();
        $requests = self::getPropertyByName('requests');

        $handler->add('/', function () { });

        // Test adding of the command
        $this->assertEquals(
            ['/'],
            array_keys($requests->getValue($handler))
        );

        $this->setExpectedException(
            'InvalidArgumentException',
            'Request callback should be callable'
        );
        $handler->add('/', 1);
    }

    public function testRunMethod()
    {
        $handler = new Handler();
        $handler->group('group/', function () use ($handler)
        {
            $handler->add('operation', function ($parameters)
            {
                return $parameters;
            });
        });

        $parameters = ['key' => 'value'];
        // Test parameters passing in to the command
        $this->assertEquals(
            $parameters,
            $handler->run('group/operation', $parameters)
        );

        $handler->middleware(
            [
                function () { return false; }
            ],
            function () use ($handler) {
                $handler->add('failed', function ()
                {
                    return 1;
                });
            },
            []
        );

        $handler->middleware(
            [
                function () { return true; }
            ],
            function () use ($handler) {
                $handler->add('success', function ()
                {
                    return 1;
                });
            },
            [
                function ($value) { return $value + 2; }
            ]
        );

        // Test failing the before middleware
        $this->assertFalse(
            $handler->run('failed', [])
        );

        // Test failing the before middleware
        $this->assertEquals(
            3,
            $handler->run('success', [])
        );

        $this->setExpectedException(
            'BadMethodCallException',
            'Route behaviour is undefined'
        );
        $handler->run('/test', []);
    }

    private static function getPropertyByName($property)
    {
        $class = new ReflectionClass('CommandHandler\Handler');
        $property = $class->getProperty($property);
        $property->setAccessible(true);

        return $property;
    }

}