# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## v5.0.0, 2018.06.05
### Added
- Support for PHP 7.0.x
- Scalar type hints

### Removed
- Support for PHP 5.x

### Changed
- Added dependency to `opis/pattern`
- This is a massive refactor of the library

## v4.1.0, 2016.01.16
### Added
- Added `Opis\Routing\Callback::getArguments` method
- Added a 3rd optional argument to `Opis\Routing\Compiler::bind` method
- Added a 2nd optional argument to `Opis\Routing\CompiledExpression::bind` method

### Changed
- Modified `Opis\Routing\Dispatcher` class
- `Opis\Routing\Router::__construct` accepts now 4th optional argument representing an array of special values.

## v4.0.0 - 2016.01.13
### Added
- Added `phpunit/phpunit` version `4.8.*` as a dependency to `require-dev`
- Added tests
- Added `Opis\Routing\Route::wrapClosures` and `Opis\Routing\Route::unwrapClosures` methods
- Added the `Opis\Routing\Router::getSpecialValues` method

### Removed
- Removed `branch-alias` property from `composer.json` file
- Removed `Opis\Routing\CompiledPattern` class
- Removed `Opis\Routing\Route::getUnmapFunction` and `Opis\Routing\Route::getMapFunction` methods

### Changed
- Updated `opis/closure` library dependency to version `^2.0.0`
- The `Opis\Routing\Route::getCompiler` is no longer a static method
- Changed the way an instance of `Opis\Routing\Route` is serialized
- The `Opis\Routing\DispatcherResolver::resolve`, `Opis\Routing\Dispatcher::dispatch` and the
`Opis\Routing\FilterInterface::pass` methods, now receive as their first argument an instance
of `Opis\Routing\Router`. All the classes implementing the `Opis\Routing\FilterInterface` interface 
were modified to support these changes.
- Modifed the `Opis\Routing\Router::route` method
- Modified the way that `Opis\Routing\Dispatcher` executes a route's callback

## v3.0.0 - 2015.07.31
### Added
- The new `Opis\Routing\Callback` class was added
- Added new exception class `Opis\Routing\CallableExpectedException`

### Removed
- All the interfaces that were under the `Opis\Routing\Contracts` namespace have been removed
- The `Opis\Routing\Dispatcher`'s `invokeAction` method was removed

### Changed
- Routes supports now all types of callable
-`FilterInterface` was moved to `Opis\Routing` namespace
- Updated `opis/closure` library dependency to version `~2.0`

### Fixed
- Fixed a major bug. `Route`, `Compiler`, `Pattern` and `CompiledPattern` classes weren't serializable.

## v2.5.0 - 2015.03.20
### Added
- Added support for late binding. 

## v2.4.3 - 2014.11.23
### Added
- Autoload file

## v2.4.3 - 2014.11.22
### Fixed
- Several bugs in `Opis\Routing\Compiler` class.

## v2.4.2 - 2014.11.12
### Changed
- Modified `Opis\Routing\CompiledRoute`

## v2.4.1 - 2014.11.11
### Added
- Added `getMapFunction` and 'getUnmapFunction' protected static methods in `Opis\Routing\Route`.

### Fixed
- Fixed a bug in `Opis\Routing\Route::serialize` method.

## v2.4.0 - 2014.10.23
### Changed
- Updated `opis/closure` library dependency to version `1.3.*`

## v2.3.1 - 2014.06.11
### Removed
- The protected static variable `$compiler` was removed.

### Fixed
- Fixed a bug in `Opis\Routing\Route`. 

## v2.3.0 - 2014.06.11
### Removed
- Removed the `Opis\Routing\Contracts\CompilerInterface` argument from `Opis\Routing\Route` constructor method.

### Changed
- The `getCompiler` method in `Opis\Routing\Contracts\RouteInterface` is now declared as static.

## v2.2.1 - 2014.06.08
### Fixed
- Fixed a major bug in `Opis\Routing\Route`

## v2.2.0 - 2014.06.04
### Added
- Started changelog
- Added `getDelimitedPattern`, `getCompiledPattern` methods to `Opis\Routing\Route`

### Changed
- Modified `Opis\Routing\CompiledExpression` constructor
- Modified `Opis\Routing\Router` class to improve performance.
- Modified `Opis\Routing\PathFilter` class to improve performance.