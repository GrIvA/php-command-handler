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
        $this->requests[implode('', $this->group) . $path] = $callback;
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
        $behaviour = $this->requests[$uri];

        return call_user_func_array($behaviour, [$parameters]);
    }

}