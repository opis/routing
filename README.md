##Opis Routing Component##

```php
use \Opis\Routing\Router;
use \Opis\Routing\Route;
use \Opis\Routing\Compiler;
use \Opis\Routing\RouteCollection;
use \Opis\Routing\Path;
use \Opis\Routing\Pattern;

$collection = new RouteCollection();
$router = new Router($collection);
$compiler =  new Compiler();

$collection[] = (new Route(new Pattern('/{a?}/{b}'), function($b, $a = 0){
    print $a.$b;    
}, $compiler))->wildcard('a', '[0-9]+')->wildcard('b', '[a-z]+');

$collection[] = (new Route(new Pattern('/{a}/{b}'), function($a, $b){
    print $a.$b;    
}, $compiler))->wildcard('a', '[a-z]+')->wildcard('b', '[0-9]+');

$router->route(new Path('/x'));
$router->route(new Path('/x/0'));
```