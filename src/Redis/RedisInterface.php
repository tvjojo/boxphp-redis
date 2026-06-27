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
 * RedisInterface Redis 连接接口
 */
namespace BoxPHP\Redis\Redis;

interface RedisInterface
{
    public function connect(string $host, int $port, float $timeout = 0): bool;
    public function disconnect(): void;
    public function isConnected(): bool;

    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 0): bool;
    public function del(string ...$keys): int;
    public function exists(string $key): bool;
    public function expire(string $key, int $ttl): bool;
    public function ttl(string $key): int;

    public function hGet(string $key, string $field): mixed;
    public function hSet(string $key, string $field, mixed $value): bool;
    public function hDel(string $key, string ...$fields): int;
    public function hGetAll(string $key): array;

    public function lPush(string $key, mixed ...$values): int;
    public function rPush(string $key, mixed ...$values): int;
    public function lPop(string $key): mixed;
    public function rPop(string $key): mixed;
    public function lLen(string $key): int;
    public function lRange(string $key, int $start, int $stop): array;

    public function sAdd(string $key, mixed ...$members): int;
    public function sMembers(string $key): array;
    public function sRem(string $key, mixed ...$members): int;

    public function incr(string $key): int;
    public function incrBy(string $key, int $increment): int;
    public function decr(string $key): int;
    public function decrBy(string $key, int $decrement): int;

    public function pipeline(callable $callback): array;
    public function multi(): void;
    public function exec(): array;
    public function discard(): void;
}
