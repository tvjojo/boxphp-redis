<?php
/**
 * RedisServiceProvider Redis 服务提供者
 */
namespace BoxPHP\Redis\Redis;

use BoxPHP\Core\Container\Container;
use BoxPHP\Core\Container\ServiceProviderInterface;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->singleton('redis.config', function () use ($container) {
            return $container->make('config')->get('redis', [
                'host' => '127.0.0.1',
                'port' => 6379,
                'password' => '',
                'database' => 0,
                'pool_max_size' => 10,
            ]);
        });

        $container->singleton(RedisPool::class, function () use ($container) {
            return new RedisPool($container->make('redis.config'));
        });

        $container->bind(RedisInterface::class, function () use ($container) {
            $pool = $container->make(RedisPool::class);
            return $pool->get();
        });
    }
}
