# php-command-handler

<img src="https://travis-ci.org/zhikiri/php-command-handler.svg?branch=master" alt="travis_ci" title="TravisCI">

Install from composer: <pre>composer require zhikiri/command-handler</pre>

Usage example here:
<pre>
$handler = new Handler();
$handler->group('main/', function () 
{
    $this->add('command, function ()
    {
        // some stuff here
    });
});
</pre>

This initialization make available action path:
<code>main/command</code>
<i>Notice: Sub groups also available</i>

Initialize commands group with some middleware actions:
<pre>
$handler = new Handler();
$handler->middleware(
    [
        function () {
            // middleware will execute before command
        },
    ],
    function () {
        // group and command initialization here
    },
    [
        function () {
            // middleware will execute after command
        },
    ],
);
</pre>

Add new middleware:
<pre>
$middleware = new Middleware();
$middleware->your_middleware = function () {
    // your middleware action code here
};

$handler = new Handler();
$handler->middleware(
    [
        $middleware->your_middleware
    ],
    function () {
        
        $this-add('command', function () 
        {
            // this action will execute after middleware `your_middleware`
        });
        
    },
    []
);
</pre>
