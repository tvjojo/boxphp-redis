<?php
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
