# Limited Pool

![Continuous Integration](https://github.com/Reactphp-parallel/limited-pool/workflows/Continuous%20Integration/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/React-parallel/limited-pool/v/stable.png)](https://packagist.org/packages/React-parallel/limited-pool)
[![Total Downloads](https://poser.pugx.org/React-parallel/limited-pool/downloads.png)](https://packagist.org/packages/React-parallel/limited-pool)
[![Code Coverage](https://scrutinizer-ci.com/g/Reactphp-parallel/limited-pool/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Reactphp-parallel/limited-pool/?branch=master)
[![Type Coverage](https://shepherd.dev/github/Reactphp-parallel/limited-pool/coverage.svg)](https://shepherd.dev/github/Reactphp-parallel/limited-pool)
[![License](https://poser.pugx.org/React-parallel/limited-pool/license.png)](https://packagist.org/packages/React-parallel/limited-pool)

ReactPHP bindings around ext-parallel

## Install ##

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require react-parallel/limited-pool 
```

## Usage

Just like any other `react-parallel` the limited pool will run any closure you send to it. With the exception that this 
pool have a fixed number of threads running.

```php
$loop = Factory::create();

$finite = new Limited(
    new Infinite($loop, new EventLoopBridge($loop), 1), // Another pool, preferably an inifinite pool
    100 // The amount of threads to start and keep running
);
$time = time();
$finite->run(function (int $time): int {
    return $time;
}, [$time])->then(function (int $time): void {
    echo 'Unix timestamp: ', $time, PHP_EOL;
})->done();
```

## License ##

Copyright 2020 [Cees-Jan Kiewiet](http://wyrihaximus.net/)

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
