CHANGELOG
-------------
### Opis Routing 2.5.0, 2015.03.20

* Added support for late binding. 

### Opis Routing 2.4.3, 2014.11.23

* Added autoload file

### Opis Routing 2.4.3, 2014.11.22

* Fixed several bugs in `Opis\Routing\Compiler` class.

### Opis Routing 2.4.2, 2014.11.12

* Modified `Opis\Routing\CompiledRoute`

### Opis Routing 2.4.1, 2014.11.11

* Fixed a bug in `Opis\Routing\Route::serialize` method.
* Added `getMapFunction` and 'getUnmapFunction' protected static methods in `Opis\Routing\Route`.

### Opis Routing 2.4.0, 2014.10.23

* Updated `opis/closure` library dependency to version `1.3.*`

### Opis Routing 2.3.1, 2014.06.11

*  Fixed a bug in `Opis\Routing\Route`. The protected static variable `$compiler` was removed.

### Opis Routing 2.3.0, 2014.06.11

* The `getCompiler` method in `Opis\Routing\Contracts\RouteInterface` is now declared as static.
* Removed the `Opis\Routing\Contracts\CompilerInterface` argument from `Opis\Routing\Route` constructor
method.

### Opis Routing 2.2.1, 2014.06.08

* Fixed a major bug in `Opis\Routing\Route`

### Opis Routing 2.2.0, 2014.06.04

* Started changelog
* Modified `Opis\Routing\CompiledExpression` constructor
* Added `getDelimitedPattern`, `getCompiledPattern` methods to `Opis\Routing\Route`
* Modified `Opis\Routing\Router` class to improve performance.
* Modified `Opis\Routing\PathFilter` class to improve performance.
