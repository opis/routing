CHANGELOG
-------------
### v4.1.0, 2016.01.16

* Added `Opis\Routing\Callback::getArguments` method
* Modified `Opis\Routing\Dispatcher` class
* `Opis\Routing\Router::__construct` accepts now 4th optional argument representing an array
of special values.
* Added a 3rd optional argument to `Opis\Routing\Compiler::bind` method
* Added a 2nd optional argument to `Opis\Routing\CompiledExpression::bind` method

### v4.0.0, 2016.01.13

* Removed `branch-alias` property from `composer.json` file
* Added `phpunit/phpunit` version `4.8.*` as a dependency to `require-dev`
* Added tests
* Updated `opis/closure` library dependency to version `^2.0.0`
* Removed `Opis\Routing\CompiledPattern` class
* The `Opis\Routing\Route::getCompiler` is no longer a static method
* Removed `Opis\Routing\Route::getUnmapFunction` and `Opis\Routing\Route::getMapFunction` methods
* Added `Opis\Routing\Route::wrapClosures` and `Opis\Routing\Route::unwrapClosures` methods
* Changed the way an instance of `Opis\Routing\Route` is serialized
* The `Opis\Routing\DispatcherResolver::resolve`, `Opis\Routing\Dispatcher::dispatch` and the
`Opis\Routing\FilterInterface::pass` methods, now receive as their first argument an instance
of `Opis\Routing\Router`. All the classes implementing the `Opis\Routing\FilterInterface` interface 
were modified to support these changes.
* Added the `Opis\Routing\Router::getSpecialValues` method
* Modifed the `Opis\Routing\Router::route` method
* The way the `Opis\Routing\Dispatcher` executes a route's callback was modified

### v3.0.0, 2015.07.31

* `FilterInterface` was moved to `Opis\Routing` namespace
* All other interfaces that were under the `Opis\Routing\Contracts` namespace have been removed
* The `Opis\Routing\Dispatcher`'s `invokeAction` method was removed
* The new `Opis\Routing\Callback` class was added
* Added new exception class `Opis\Routing\CallableExpectedException`
* Routes supports now all types of callable
* Fixed a major bug. `Route`, `Compiler`, `Pattern` and `CompiledPattern` classes weren't serializable.
* Updated `opis/closure` library dependency to version `~2.0`

### v2.5.0, 2015.03.20

* Added support for late binding. 

### v2.4.3, 2014.11.23

* Added autoload file

### v2.4.3, 2014.11.22

* Fixed several bugs in `Opis\Routing\Compiler` class.

### v2.4.2, 2014.11.12

* Modified `Opis\Routing\CompiledRoute`

### v2.4.1, 2014.11.11

* Fixed a bug in `Opis\Routing\Route::serialize` method.
* Added `getMapFunction` and 'getUnmapFunction' protected static methods in `Opis\Routing\Route`.

### v2.4.0, 2014.10.23

* Updated `opis/closure` library dependency to version `1.3.*`

### v2.3.1, 2014.06.11

*  Fixed a bug in `Opis\Routing\Route`. The protected static variable `$compiler` was removed.

### v2.3.0, 2014.06.11

* The `getCompiler` method in `Opis\Routing\Contracts\RouteInterface` is now declared as static.
* Removed the `Opis\Routing\Contracts\CompilerInterface` argument from `Opis\Routing\Route` constructor
method.

### v2.2.1, 2014.06.08

* Fixed a major bug in `Opis\Routing\Route`

### v2.2.0, 2014.06.04

* Started changelog
* Modified `Opis\Routing\CompiledExpression` constructor
* Added `getDelimitedPattern`, `getCompiledPattern` methods to `Opis\Routing\Route`
* Modified `Opis\Routing\Router` class to improve performance.
* Modified `Opis\Routing\PathFilter` class to improve performance.
