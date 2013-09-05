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
