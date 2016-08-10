# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## v5.0.x-dev
### Added
- Support for PHP 7.0.x
- Scalar type hints
- New constants to `Opis\Routing\Compiler` class
- Method `Opis\Routing\Compiler::getOptions`
- Method `Opis\Routing\Router::match`
- Method `Opis\Routing\Router::extract`
- Method `Opis\Routing\Router::bind`
- Method `Opis\Routing\Router::buildArguments`
- Method `Opis\Routing\Route::setRouteCollection`
- New methods to `Opis\Routing\Route` class
- `Opis\Routing\DispatcherCollection` class
- `Opis\Routing\FilterCollection` class
- `Opis\Routing\RouteCollection` class
- `Opis\Routing\ClosureWrapperTrait` trait
- `Opis\Routing\DispatcherTrait` trait
- `Opis\Routing\DispatcherInterface` interface

### Removed
- Support for PHP 5.x
- `Opis\Routing\Pattern` class
- `Opis\Routing\CallableExpectedException` class
- `Opis\Routing\CompiledExpression` class
- `Opis\Routing\CompiledRoute` class
- `Opis\Routing\Route::getCompiler` method
- `Opis\Routing\Route::getCompiledPattern` method
- `Opis\Routing\Route::getDelimitedPattern` method
- `Opis\Routing\Route::compile` method
- `Opis\Routing\Filter` class
- `Opis\Routing\PathFilter` class
- `Opis\Routing\Collections\AbstractCollection` class
- `Opis\Routing\Collections\DispatcherCollection` class
- `Opis\Routing\Collections\FilterCollection` class
- `Opis\Routing\Collections\RouteCollection` class
- `Opis\Routing\Compiler::build` method
- `Opis\Routing\Compiler::delimit` method
- `Opis\Routing\Compiler::extract` method

### Changed
- The constructor of `Opis\Routing\Compiler` class now takes as an argument a single array of options
- Method `Opis\Routing\Compiler::compile` was renamed to `getRegex`
- Method `Opis\Routing\Compiler::values` was renamed to `getValues`
- Method `Opis\Routing\Compiler::names` was renamed to `getNames`
- `Opis\Routing\Compiler` no longer implements the `Serializable` interface
- Protected method `Opis\Routing\Compiler::wrapClosures` is now static
- Protected method `Opis\Routing\Compiler::unwrapClosures` is now static
- `Opis\Routing\FilterInterface::pass` method
- The constructor of the`Opis\Routing\Router` class
- `Opis\Routing\Path` class was renamed to `Opis\Routing\Context`

### Fixed
- Nothing

## v4.1.0, 2016.01.16
### Added
- Added `Opis\Routing\Callback::getArguments` method
- Added a 3rd optional argument to `Opis\Routing\Compiler::bind` method
- Added a 2nd optional argument to `Opis\Routing\CompiledExpression::bind` method

### Removed
- Nothing

### Changed
- Modified `Opis\Routing\Dispatcher` class
- `Opis\Routing\Router::__construct` accepts now 4th optional argument representing an array of special values.

### Fixed
- Nothing

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

### Fixed
- Nothing

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

### Removed
- Nothing

### Changed
- Nothing

### Fixed
- Nothing

## v2.4.3 - 2014.11.23
### Added
- Autoload file

### Removed
- Nothing

### Changed
- Nothing

### Fixed
- Nothing

## v2.4.3 - 2014.11.22
### Added
- Nothing

### Removed
- Nothing

### Changed
- Nothing

### Fixed
- Several bugs in `Opis\Routing\Compiler` class.

## v2.4.2 - 2014.11.12
### Added
- Nothing

### Removed
- Nothing

### Changed
- Modified `Opis\Routing\CompiledRoute`

### Fixed
- Nothing

## v2.4.1 - 2014.11.11
### Added
- Added `getMapFunction` and 'getUnmapFunction' protected static methods in `Opis\Routing\Route`.

### Removed
- Nothing

### Changed
- Nothing

### Fixed
- Fixed a bug in `Opis\Routing\Route::serialize` method.

## v2.4.0 - 2014.10.23
### Added
- Nothing

### Removed
- Nothing

### Changed
- Updated `opis/closure` library dependency to version `1.3.*`

### Fixed
- Nothing

## v2.3.1 - 2014.06.11
### Added
- Nothing

### Removed
- The protected static variable `$compiler` was removed.

### Changed
- Nothing

### Fixed
- Fixed a bug in `Opis\Routing\Route`. 

## v2.3.0 - 2014.06.11
### Added
- Nothing

### Removed
- Removed the `Opis\Routing\Contracts\CompilerInterface` argument from `Opis\Routing\Route` constructor method.

### Changed
- The `getCompiler` method in `Opis\Routing\Contracts\RouteInterface` is now declared as static.

### Fixed
- Nothing

## v2.2.1 - 2014.06.08
### Added
- Nothing

### Removed
- Nothing

### Changed
- Nothing

### Fixed
- Fixed a major bug in `Opis\Routing\Route`

## v2.2.0 - 2014.06.04
### Added
- Started changelog
- Added `getDelimitedPattern`, `getCompiledPattern` methods to `Opis\Routing\Route`

### Removed
- Nothing

### Changed
- Modified `Opis\Routing\CompiledExpression` constructor
- Modified `Opis\Routing\Router` class to improve performance.
- Modified `Opis\Routing\PathFilter` class to improve performance.

### Fixed
- Nothing