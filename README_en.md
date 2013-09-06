# PHP-Cluster

Cluster is used to manage your workers under CLI.

# Install

Add `"pagon/cluster": "*"` to your [`composer.json`](http://getcomposer.org).

```
composer.phar install
```

# Use

## Simple

```php
$cluster = new Cluster();

if ($cluster->isMaster()) {
    $cluster->fork(__FILE__);
    $cluster->run();
} else {
    // Process something
}
```

## Advance

```php
$cluster = new Cluster();

// Set max children to work
$cluster->setMaxChildren(3);

if ($cluster->isMaster()) {
    // When process is forked
    $cluster->on('fork', function($worker){
        $worker->send();
    });

    // When process exit
    $cluster->on('exit', function($worker, $code){
        // $worker died;
    });

    // When receive a message from worker
    $cluster->on('message', function($worker, $code){
        // $worker send me a message
    });

    // Fork a worker
    $worker = $cluster->fork(__FILE__);

    // Run forever
    $cluster->run();
} else {
    // Process something in work
}
```

# License

(The MIT License)

Copyright (c) 2012 hfcorriez &lt;hfcorriez@gmail.com&gt;

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.