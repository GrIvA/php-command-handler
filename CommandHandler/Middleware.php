<?php

namespace CommandHandler;

class Middleware
{

    private $before = [];
    private $after = [];

    private $middleware = [];

    public function __isset($property)
    {
        return isset($this->middleware[$property]);
    }

    public function __unset($property)
    {
        unset($this->middleware[$property]);
    }

    public function __call($property, $arguments)
    {
        return call_user_func_array($this->middleware[$property], $arguments);
    }

    public function __get($property)
    {
        return $this->middleware[$property];
    }

    public function __set($property, $value)
    {
        $filtered = self::callbackFilter([$value]);
        if (empty($filtered)) {
            throw new \InvalidArgumentException('Middleware should be callable');
        }
        $this->middleware[$property] = $value;
    }

    /**
     * Middleware constructor.
     * @param array $before list of before middleware actions
     * @param array $after list of after middleware actions
     */
    public function __construct(array $before, array $after)
    {
        $this->before = self::callbackFilter($before);
        $this->after = self::callbackFilter($after);
    }

    /**
     * Add some middleware actions to the before and after list
     * @param array $before list of additional middleware actions for before list
     * @param array $after list of additional middleware actions for after list
     */
    public function add(array $before, array $after)
    {
        call_user_func_array([$this, 'addBefore'], $before);
        call_user_func_array([$this, 'addAfter'], $after);
    }

    /**
     * Add list of before middleware
     */
    public function addBefore()
    {
        $this->before = array_merge(
            $this->before,
            self::callbackFilter(func_get_args())
        );
    }

    /**
     * Add list of after middleware
     */
    public function addAfter()
    {
        $this->after = array_merge(
            $this->after,
            self::callbackFilter(func_get_args())
        );
    }

    /**
     * Execute the list of before middleware actions
     * @return bool|mixed|null middleware execution result
     */
    public function before()
    {
        return $this->call($this->before);
    }

    /**
     * Executre the list of after middleware actions
     * @param string $value command execution result
     * @return bool|mixed|null middleware execution result
     */
    public function after($value)
    {
        return $this->call($this->after, $value);
    }

    /**
     * Execute the list of middleware
     * @param array $list list of middleware actions
     * @param null $result start value of accumulator
     * @return bool|mixed|null execution result
     */
    private function call(array $list, $result = null)
    {
        foreach ($list as $item) {
            $result = call_user_func_array($item, [$result]);
            if ($result === false) {
                return false;
            }
        }

        return $result;
    }

    /**
     * Filter passing list by callable function
     * @param array $list target list of actions
     * @return array filtered list of callable items
     */
    private static function callbackFilter(array $list)
    {
        return array_filter($list, 'is_callable');
    }

}