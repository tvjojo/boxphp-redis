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
 * RedisConnection Redis 连接
 */
namespace BoxPHP\Redis\Redis;

class RedisConnection implements RedisInterface
{
    protected ?\Redis $redis = null;
    protected string $host = '127.0.0.1';
    protected int $port = 6379;
    protected string $password = '';
    protected int $database = 0;
    protected float $timeout = 2.0;
    protected bool $connected = false;

    public function __construct(array $config = [])
    {
        $this->host = $config['host'] ?? '127.0.0.1';
        $this->port = $config['port'] ?? 6379;
        $this->password = $config['password'] ?? '';
        $this->database = $config['database'] ?? 0;
        $this->timeout = $config['timeout'] ?? 2.0;

        if (!extension_loaded('redis')) {
            throw new \RuntimeException('Redis extension not loaded');
        }
    }

    public function connect(string $host, int $port, float $timeout = 0): bool
    {
        $this->redis = new \Redis();
        $result = $this->redis->connect($host ?: $this->host, $port ?: $this->port, $timeout ?: $this->timeout);

        if ($result && $this->password !== '') {
            $result = $this->redis->auth($this->password);
        }

        if ($result && $this->database > 0) {
            $this->redis->select($this->database);
        }

        $this->connected = $result;
        return $result;
    }

    public function disconnect(): void
    {
        if ($this->redis) {
            $this->redis->close();
            $this->redis = null;
            $this->connected = false;
        }
    }

    public function isConnected(): bool
    {
        return $this->connected && $this->redis !== null;
    }

    public function getRedis(): ?\Redis
    {
        return $this->redis;
    }

    public function ensureConnected(): void
    {
        if (!$this->isConnected()) {
            $this->connect($this->host, $this->port, $this->timeout);
        }
    }

    // ===== String =====

    public function get(string $key): mixed
    {
        $this->ensureConnected();
        return $this->redis->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $this->ensureConnected();
        $encoded = is_array($value) || is_object($value) ? json_encode($value) : $value;
        if ($ttl > 0) {
            $this->redis->setex($key, $ttl, $encoded);
        } else {
            $this->redis->set($key, $encoded);
        }
        return true;
    }

    public function del(string ...$keys): int
    {
        $this->ensureConnected();
        return $this->redis->del(...$keys);
    }

    public function exists(string $key): bool
    {
        $this->ensureConnected();
        return (bool)$this->redis->exists($key);
    }

    public function expire(string $key, int $ttl): bool
    {
        $this->ensureConnected();
        return $this->redis->expire($key, $ttl);
    }

    public function ttl(string $key): int
    {
        $this->ensureConnected();
        return $this->redis->ttl($key);
    }

    // ===== Hash =====

    public function hGet(string $key, string $field): mixed
    {
        $this->ensureConnected();
        return $this->redis->hGet($key, $field);
    }

    public function hSet(string $key, string $field, mixed $value): bool
    {
        $this->ensureConnected();
        return (bool)$this->redis->hSet($key, $field, is_array($value) || is_object($value) ? json_encode($value) : $value);
    }

    public function hDel(string $key, string ...$fields): int
    {
        $this->ensureConnected();
        return $this->redis->hDel($key, ...$fields);
    }

    public function hGetAll(string $key): array
    {
        $this->ensureConnected();
        return $this->redis->hGetAll($key);
    }

    // ===== List =====

    public function lPush(string $key, mixed ...$values): int
    {
        $this->ensureConnected();
        $encoded = array_map(fn($v) => is_array($v) || is_object($v) ? json_encode($v) : $v, $values);
        return $this->redis->lPush($key, ...$encoded);
    }

    public function rPush(string $key, mixed ...$values): int
    {
        $this->ensureConnected();
        $encoded = array_map(fn($v) => is_array($v) || is_object($v) ? json_encode($v) : $v, $values);
        return $this->redis->rPush($key, ...$encoded);
    }

    public function lPop(string $key): mixed
    {
        $this->ensureConnected();
        return $this->redis->lPop($key);
    }

    public function rPop(string $key): mixed
    {
        $this->ensureConnected();
        return $this->redis->rPop($key);
    }

    public function lLen(string $key): int
    {
        $this->ensureConnected();
        return $this->redis->lLen($key);
    }

    public function lRange(string $key, int $start, int $stop): array
    {
        $this->ensureConnected();
        return $this->redis->lRange($key, $start, $stop);
    }

    // ===== Set =====

    public function sAdd(string $key, mixed ...$members): int
    {
        $this->ensureConnected();
        $encoded = array_map(fn($v) => is_array($v) || is_object($v) ? json_encode($v) : $v, $members);
        return $this->redis->sAdd($key, ...$encoded);
    }

    public function sMembers(string $key): array
    {
        $this->ensureConnected();
        return $this->redis->sMembers($key);
    }

    public function sRem(string $key, mixed ...$members): int
    {
        $this->ensureConnected();
        return $this->redis->sRem($key, ...$members);
    }

    // ===== Counter =====

    public function incr(string $key): int
    {
        $this->ensureConnected();
        return $this->redis->incr($key);
    }

    public function incrBy(string $key, int $increment): int
    {
        $this->ensureConnected();
        return $this->redis->incrBy($key, $increment);
    }

    public function decr(string $key): int
    {
        $this->ensureConnected();
        return $this->redis->decr($key);
    }

    public function decrBy(string $key, int $decrement): int
    {
        $this->ensureConnected();
        return $this->redis->decrBy($key, $decrement);
    }

    // ===== Transaction =====

    public function pipeline(callable $callback): array
    {
        $this->ensureConnected();
        $this->redis->multi(\Redis::PIPELINE);
        try {
            $callback($this);
            return $this->redis->exec();
        } catch (\Throwable $e) {
            $this->redis->discard();
            throw $e;
        }
    }

    public function multi(): void
    {
        $this->ensureConnected();
        $this->redis->multi();
    }

    public function exec(): array
    {
        return $this->redis->exec();
    }

    public function discard(): void
    {
        $this->redis->discard();
    }

    // ===== Utility =====

    public function ping(): bool
    {
        $this->ensureConnected();
        $result = $this->redis->ping();
        return $result === '+PONG' || $result === 'PONG' || $result === '+PONG';
    }

    public function info(string $section = ''): array
    {
        $this->ensureConnected();
        return $this->redis->info($section);
    }

    public function dbSize(): int
    {
        $this->ensureConnected();
        return $this->redis->dbSize();
    }

    public function flushDb(): bool
    {
        $this->ensureConnected();
        return $this->redis->flushDB();
    }
}
