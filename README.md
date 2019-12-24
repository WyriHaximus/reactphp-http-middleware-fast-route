# ReactPHP HTTP Server Fast Route Middleware

[![Build Status](https://travis-ci.com/WyriHaximus/reactphp-http-middleware-fast-route.svg?branch=master)](https://travis-ci.com/WyriHaximus/reactphp-http-middleware-fast-route)
[![Latest Stable Version](https://poser.pugx.org/wyrihaximus/reactphp-http-middleware-fast-route/v/stable.png)](https://packagist.org/packages/wyrihaximus/react-http-middleware-fast-route)
[![Total Downloads](https://poser.pugx.org/wyrihaximus/react-http-middleware-fast-route/downloads.png)](https://packagist.org/packages/wyrihaximus/react-http-middleware-fast-route/stats)
[![Code Coverage](https://scrutinizer-ci.com/g/wyrihaximus/reactphp-http-middleware-fast-route/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wyrihaximus/reactphp-http-middleware-fast-route/?branch=master)
[![License](https://poser.pugx.org/wyrihaximus/reactphp-http-middleware-fast-route/license.png)](https://packagist.org/packages/wyrihaximus/reactphp-http-middleware-fast-route)
[![PHP 7 ready](http://php7ready.timesplinter.ch/WyriHaximus/reactphp-http-middleware-fast-route/badge.svg)](https://travis-ci.com/WyriHaximus/reactphp-http-middleware-fast-route)

# Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require wyrihaximus/react-http-middleware-fast-route
```

# Usage

This middleware only detects the correct route for you or returns a 404/405 when failing, you still have to call the handler yourself.

```php
$middleware = new FastRouteMiddleware([
  new Route('GET', '/user/{name}/{id:[0-9]+}', function (ServerRequestInterface $request): PromiseInterface {
      return resolve(new Response(200));
  }, [
      'attribute' => 'value',
  ]),
]);
```

# Attributes

This middleware sets two attributes when passing on the request to the next middleware:
- `Attributes::HANDLER`: The handler specified in the Route VO.
- `Attributes::ANNOTATIONS`: Any annotations passed into the Route VO.

# License

The MIT License (MIT)

Copyright (c) 2019 Cees-Jan Kiewiet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
