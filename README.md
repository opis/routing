Opis Routing
=================
[![Latest Stable Version](https://poser.pugx.org/opis/routing/version.png)](https://packagist.org/packages/opis/routing)
[![Latest Unstable Version](https://poser.pugx.org/opis/routing/v/unstable.png)](//packagist.org/packages/opis/routing)
[![License](https://poser.pugx.org/opis/routing/license.png)](https://packagist.org/packages/opis/routing)

A framework for building routing components
------------------------------

### Installation

This library is available on [Packagist](https://packagist.org/packages/opis/routing) and can be installed using [Composer](http://getcomposer.org)

```json
{
    "require": {
        "opis/routing": "2.1.*"
    }
}
```

### Documentation

### Examples

```php
use \Opis\Routing\Router;
use \Opis\Routing\Route;
use \Opis\Routing\Compiler;
use \Opis\Routing\Collections\RouteCollection;
use \Opis\Routing\Path;
use \Opis\Routing\Pattern;


function route($pattern, Closure $callback)
{
    static $compiler = null;
    
    if($compiler === null)
    {
        $compiler = new Compiler();
    }
    
    return new Route(new Pattern($pattern), $callback, $compiler);
}

$collection = new RouteCollection();

$collection[] = route('/{text}/{from?}', function($output){
    return $output;
})
->wildcard('text', '[a-z]+')
->implicit('from', 'OPIS')
->bind('output', function($text, $from){
    return 'Hello ' . strtoupper($text) . ' from ' . strtolower($from);
});

$router = new Router($collection);
print $router->route(new Path('/world')); //> Hello WORLD from opis

//Serialize & unseialize
$collection = unserialize(serialize($collection));

$router = new Router($collection);
print $router->route(new Path('/world/MARS')); //> Hello WORLD from mars
```