Opis Routing
=================
[![Build Status](https://travis-ci.org/opis/routing.svg?branch=master)](https://travis-ci.org/opis/routing)
[![Latest Stable Version](https://poser.pugx.org/opis/routing/version.png)](https://packagist.org/packages/opis/routing)
[![Latest Unstable Version](https://poser.pugx.org/opis/routing/v/unstable.png)](//packagist.org/packages/opis/routing)
[![License](https://poser.pugx.org/opis/routing/license.png)](https://packagist.org/packages/opis/routing)

Routing framework
------------------
**Opis Routing** is a framework for building various components that need 
routing support. In contrast to other routing libraries,
it may handle anything that follows a pattern and is not limited to HTTP request.

Although this library was conceived to be embedded by other libraries that 
need routing capabilities and not as a standalone library, 
the usage of this library as a standalone library is not discouraged.

### Documentation

The full documentation for this library can be found [here][documentation]

### License

**Opis Routing** is licensed under the [Apache License, Version 2.0][apache_license]. 

### Requirements

* PHP 7.0.* or higher
* [Opis Closure] ^3.0

## Installation

**Opis Routing** is available on [Packagist] and it can be installed from a 
command line interface by using [Composer]. 

```bash
composer require opis/container
```

Or you could directly reference it into your `composer.json` file as a dependency

```json
{
    "require": {
        "opis/routing": "^3.0"
    }
}
```

[documentation]: https://www.opis.io/container
[apache_license]: https://www.apache.org/licenses/LICENSE-2.0 "Apache License"
[Packagist]: https://packagist.org/packages/opis/container "Packagist"
[Composer]: https://getcomposer.org "Composer"
[Opis Closure]: https://www.opis.io/closure