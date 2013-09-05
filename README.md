其他语言：[English](README_en.md)

# PHP-Cluster

Cluster用来在CLI模式下管理工作进程，支持中间件方便扩展

# 安装

添加 `"pagon/cluster": "*"` 到 [`composer.json`](http://getcomposer.org).

```
composer.phar install
```

# 使用

## 简单方式

```php
$cluster = new Cluster();

if ($cluster->isMaster()) {
    $cluster->fork(__FILE__);
    $cluster->run();
} else {
    // 处理工作
}
```

## 高级使用

```php
$cluster = new Cluster();

// 设置最多可运行的worker数量
$cluster->setMaxChildren(3);

if ($cluster->isMaster()) {

    // 当生成进程时
    $cluster->on('fork', function($worker){
        $worker->send();
    });

    // 当退出时
    $cluster->on('exit', function($worker, $code){
        // $worker 退出
    });

    // 当收到消息时
    $cluster->on('message', function($worker, $code){
        // $worker 发了一个消息
    });

    // Fork一个worker出来
    $worker = $cluster->fork(__FILE__);

    // 运行起来
    $cluster->run();
} else {
    // 处理工作
}
```
