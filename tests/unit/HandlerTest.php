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

    public function testAddMethod()
    {
        $handler = new Handler();
        $requests = self::getPropertyByName('requests');

        $handler->add('/', function () { echo 1; });
        $this->assertEquals(
            ['/' => function () { echo 1; }],
            $requests->getValue($handler)
        );

        $this->setExpectedException(
            'InvalidArgumentException',
            'Request callback should be callable'
        );
        $handler->add('/', 1);
    }

    public function testGroupMethod()
    {
        $handler = new Handler();
        $requests = self::getPropertyByName('requests');

        $handler->group('group_test/', function () use ($handler)
        {
            $handler->add('first', function () {});
            $handler->add('second', function () {});

            $handler->group('inner_group/', function () use ($handler) {

                $handler->add('third', function () {});

            });

        });
        $this->assertEquals(
            [
                'group_test/first' => function () {},
                'group_test/second' => function () {},
                'group_test/inner_group/third' => function () {},
            ],
            $requests->getValue($handler)
        );

        $this->setExpectedException(
            'InvalidArgumentException',
            'Group callback should be callable'
        );
        $handler->group('/', 1);
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
        $this->assertEquals(
            $parameters,
            $handler->run('group/operation', $parameters)
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