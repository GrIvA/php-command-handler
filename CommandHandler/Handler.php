<?php

namespace CommandHandler;

/**
 * Class Handler
 * @package CommandHandler
 */
class Handler
{

    private $requests = [];
    private $group = [];

    private $middleware;

    /**
     * Add a middleware for a group/command
     * @param array $before list of middleware than will be execute before the command
     * @param \Closure $declaration group and command initialization
     * @param array $after list of middleware than will be execute after the command
     */
    public function middleware($before = [], \Closure $declaration, $after = [])
    {
        $current_middleware = $this->middleware;
        if (!$current_middleware instanceof Middleware) {
            $this->middleware = new Middleware($before, $after);
        } else {
            $this->middleware = clone $current_middleware;
            $this->middleware->add($before, $after);
        }
        call_user_func_array($declaration, []);
        $this->middleware = $current_middleware != $this->middleware
            ? $current_middleware
            : null;
    }

    /**
     * Add new command group
     * @param string $path command group name
     * @param string|\Closure $callback group initialization stuff
     */
    public function group($path, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Group callback should be callable');
        }
        array_push($this->group, $path);
        if ($callback instanceof \Closure) {
            $callback = $callback->bindTo($this);
        }
        call_user_func_array($callback, []);
        array_pop($this->group);
    }

    /**
     * Add new command
     * @param string $path command name
     * @param string|\Closure $callback command details
     */
    public function add($path, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Request callback should be callable');
        }
        $this->requests[implode('', $this->group) . $path] = [
            'middleware' => $this->middleware,
            'callback' => $callback
        ];
    }

    /**
     * Execute command with parameters
     * @param string $uri path to the targt command
     * @param array $parameters list of command parameters
     * @return mixed command execution result
     */
    public function run($uri, $parameters = [])
    {
        if (!isset($this->requests[$uri])) {
            throw new \BadMethodCallException('Route behaviour is undefined');
        }
        $middleware = $this->requests[$uri]['middleware'];
        if ($middleware instanceof Middleware && !$middleware->before()) {
            return false;
        }
        $result = call_user_func_array(
            $this->requests[$uri]['callback'],
            [$parameters]
        );

        return $middleware instanceof Middleware
            ? $middleware->after($result)
            : $result;
    }

}