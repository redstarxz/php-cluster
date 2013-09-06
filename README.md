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