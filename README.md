# boxphp/redis

BoxPHP Redis 包 - Redis 连接、连接池及服务提供者

## 安装

```bash
composer require boxphp/redis
```

## 前置要求

- PHP Redis 扩展 (`phpredis`)

## 使用

### 基础连接

```php
use BoxPHP\Redis\Redis\RedisConnection;

$redis = new RedisConnection([
    'host' => '127.0.0.1',
    'port' => 6379,
    'timeout' => 2.0,
]);

$redis->connect();
$redis->set('name', 'John');
echo $redis->get('name'); // John
```

### 连接池

```php
use BoxPHP\Redis\Redis\RedisPool;

$pool = new RedisPool([
    'host' => '127.0.0.1',
    'port' => 6379,
    'pool_max_size' => 10,
]);

$redis = $pool->get();
$redis->set('key', 'value');
$pool->put($redis);
```

### 服务提供者（配合 Container）

```php
use BoxPHP\Core\Container\Container;
use BoxPHP\Redis\Redis\RedisServiceProvider;

$container = new Container();
$provider = new RedisServiceProvider();
$provider->register($container);

// 使用
$redis = $container->make(RedisConnection::class);
```

### 支持的操作

```php
// String
$redis->set('key', 'value');
$redis->get('key');
$redis->del('key');
$redis->incr('counter');

// Hash
$redis->hSet('user:1', 'name', 'John');
$redis->hGet('user:1', 'name');
$redis->hGetAll('user:1');

// List
$redis->lPush('queue', 'task1');
$redis->rPop('queue');
$redis->lRange('queue', 0, -1);

// Set
$redis->sAdd('tags', 'php', 'redis');
$redis->sMembers('tags');

// Pipeline
$redis->pipeline(function ($redis) {
    $redis->set('a', 1);
    $redis->set('b', 2);
    $redis->set('c', 3);
});
```

## 依赖

- PHP >= 8.1
- boxphp/core ^1.0
