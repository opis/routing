##Opis Routing##

```php
use \Opis\Routing\Example\Route;
use \Opis\Routing\Example\Router;
use \Opis\Routing\Example\RouteCollection;


$collection = new RouteCollection();

$collection[] = Route::create('/{user}', function($user) {
        print $user;
    })
    ->where('user', '[a-z]+')
    ->bind('user', function($value){
	return strtoupper($value);
    });
  
$router = new Router('/opis', $collection);

$router->execute();
```

Output

```
OPIS
```