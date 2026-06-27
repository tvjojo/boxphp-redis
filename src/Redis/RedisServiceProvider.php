<?php
/**
 * BoxPHP Framework
 *
 * Copyright 2026 BoxPHP
 * By tvjojo, asterhuang, 黄波涛; 5viv.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
