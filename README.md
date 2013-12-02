##Opis Routing##

```php
use \Opis\Routing\Route;
use \Opis\Routing\RouteCollection;
use \Opis\Routing\Http\Router;

$collection  = new RouteCollection();

$route = new Route('/{alpha}', function($text){
    print $text;
});

$route->placeholder('alpha', '[a-zA-Z]+')
      ->bind('alpha', function($value){ return strtoupper($value);});

$collection->add($route);

$router = new Router('/opis', $collection);

$router->run();
```

Output

```
OPIS
```