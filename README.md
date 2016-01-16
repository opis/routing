Opis Routing
=================
[![Build Status](https://travis-ci.org/opis/routing.svg?branch=master)](https://travis-ci.org/opis/routing)
[![Latest Stable Version](https://poser.pugx.org/opis/routing/version.png)](https://packagist.org/packages/opis/routing)
[![Latest Unstable Version](https://poser.pugx.org/opis/routing/v/unstable.png)](//packagist.org/packages/opis/routing)
[![License](https://poser.pugx.org/opis/routing/license.png)](https://packagist.org/packages/opis/routing)

Routing framework
------------------
**Opis Routing** is a framework for building various routing components. In contrast to other routing libraries,
it may handle anything that follows a pattern and is not limited only to HTTP request.

This library was conceived to be embedded by other libraries that need routing capabilities and not as a
standalone library; although the usage of this library as a standalone library is not discouraged. 

### License

**Opis Routing** is licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0). 

### Requirements

* PHP 5.3.* or higher
* [Opis Closure](http://www.opis.io/closure) ^2.0.0

### Installation

This library is available on [Packagist](https://packagist.org/packages/opis/routing) and can be installed using [Composer](http://getcomposer.org).

```json
{
    "require": {
        "opis/routing": "^4.1.0"
    }
}
```

If you are unable to use [Composer](http://getcomposer.org) you can download the
[tar.gz](https://github.com/opis/routing/archive/4.1.0.tar.gz) or the [zip](https://github.com/opis/routing/archive/4.1.0.zip)
archive file, extract the content of the archive and include de `autoload.php` file into your project. 

```php

require_once 'path/to/routing-4.1.0/autoload.php';

```

### Documentation

Examples and documentation can be found at http://opis.io/routing .