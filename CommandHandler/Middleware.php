<?php

namespace CommandHandler;

class Middleware
{

    private $before = [];
    private $after = [];

    public function __construct(array $before, array $after)
    {
        $this->before = $this->callbackFilter($before);
        $this->after = $this->callbackFilter($after);
    }

    public function add(array $before, array $after)
    {
        $this->addBefore($before);
        $this->addAfter($after);
    }

    public function addBefore()
    {
        $this->before = array_merge(
            $this->before,
            $this->callbackFilter(func_get_args())
        );
    }

    public function addAfter()
    {
        $this->after = array_merge(
            $this->after,
            $this->callbackFilter(func_get_args())
        );
    }

    public function before()
    {
        return $this->call($this->before);
    }

    public function after($value)
    {
        return $this->call($this->after, $value);
    }

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

    private function callbackFilter(array $list)
    {
        return array_filter($list, function ($item)
        {
            return is_callable($item);
        });
    }

}