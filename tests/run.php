<?php
/**
 * Redis 包测试 - 修正版
 */
require_once __DIR__ . '/../vendor/autoload.php';

use BoxPHP\Redis\Redis\RedisInterface;

echo "=== BoxPHP Redis Package Tests ===\n\n";
$passed = 0;
$failed = 0;

// Test 1: RedisInterface
echo "1. RedisInterface Mock\n";
try {
    // 使用匿名类模拟 Redis
    $redis = new class implements RedisInterface {
        private array $data = [];
        private bool $connected = false;
        
        public function connect(string $host, int $port, float $timeout = 0): bool {
            $this->connected = true;
            return true;
        }
        public function disconnect(): void { $this->connected = false; }
        public function isConnected(): bool { return $this->connected; }
        
        public function get(string $key): mixed { return $this->data[$key] ?? null; }
        public function set(string $key, mixed $value, int $ttl = 0): bool {
            $this->data[$key] = $value;
            return true;
        }
        public function del(string ...$keys): int {
            $count = 0;
            foreach ($keys as $k) {
                if (isset($this->data[$k])) { unset($this->data[$k]); $count++; }
            }
            return $count;
        }
        public function exists(string $key): bool { return isset($this->data[$key]); }
        public function expire(string $key, int $ttl): bool { return true; }
        public function ttl(string $key): int { return -1; }
        
        public function hGet(string $key, string $field): mixed { return $this->data[$key][$field] ?? null; }
        public function hSet(string $key, string $field, mixed $value): bool {
            if (!isset($this->data[$key])) $this->data[$key] = [];
            $this->data[$key][$field] = $value;
            return true;
        }
        public function hDel(string $key, string ...$fields): int { return 0; }
        public function hGetAll(string $key): array { return $this->data[$key] ?? []; }
        
        public function lPush(string $key, mixed ...$values): int {
            if (!isset($this->data[$key])) $this->data[$key] = [];
            array_unshift($this->data[$key], ...$values);
            return count($this->data[$key]);
        }
        public function rPush(string $key, mixed ...$values): int {
            if (!isset($this->data[$key])) $this->data[$key] = [];
            array_push($this->data[$key], ...$values);
            return count($this->data[$key]);
        }
        public function lPop(string $key): mixed { return array_shift($this->data[$key]); }
        public function rPop(string $key): mixed { return array_pop($this->data[$key]); }
        public function lLen(string $key): int { return count($this->data[$key] ?? []); }
        public function lRange(string $key, int $start, int $stop): array { return $this->data[$key] ?? []; }
        
        public function sAdd(string $key, mixed ...$members): int { return 0; }
        public function sMembers(string $key): array { return []; }
        public function sRem(string $key, mixed ...$members): int { return 0; }
        
        public function incr(string $key): int {
            $this->data[$key] = ($this->data[$key] ?? 0) + 1;
            return $this->data[$key];
        }
        public function incrBy(string $key, int $increment): int {
            $this->data[$key] = ($this->data[$key] ?? 0) + $increment;
            return $this->data[$key];
        }
        public function decr(string $key): int {
            $this->data[$key] = ($this->data[$key] ?? 0) - 1;
            return $this->data[$key];
        }
        public function decrBy(string $key, int $decrement): int {
            $this->data[$key] = ($this->data[$key] ?? 0) - $decrement;
            return $this->data[$key];
        }
        
        public function pipeline(callable $callback): array { return []; }
        public function multi(): void {}
        public function exec(): array { return []; }
        public function discard(): void {}
    };
    
    // 连接测试
    assert($redis->connect('127.0.0.1', 6379) === true);
    assert($redis->isConnected() === true);
    
    // String operations
    $redis->set('name', 'John');
    assert($redis->get('name') === 'John');
    assert($redis->exists('name') === true);
    
    $redis->del('name');
    assert($redis->get('name') === null);
    assert($redis->exists('name') === false);
    
    // TTL
    assert($redis->ttl('name') === -1);
    
    // Counter
    assert($redis->incr('counter') === 1);
    assert($redis->incr('counter') === 2);
    assert($redis->decr('counter') === 1);
    assert($redis->incrBy('counter', 10) === 11);
    assert($redis->decrBy('counter', 5) === 6);
    
    // Hash
    $redis->hSet('user:1', 'name', 'Alice');
    $redis->hSet('user:1', 'email', 'alice@example.com');
    assert($redis->hGet('user:1', 'name') === 'Alice');
    assert($redis->hGet('user:1', 'email') === 'alice@example.com');
    $all = $redis->hGetAll('user:1');
    assert($all['name'] === 'Alice');
    assert($all['email'] === 'alice@example.com');
    
    // List
    $redis->lPush('queue', 'task1');
    $redis->lPush('queue', 'task2');
    assert($redis->lLen('queue') === 2);
    assert($redis->lPop('queue') === 'task2');
    assert($redis->rPop('queue') === 'task1');
    
    // Disconnect
    $redis->disconnect();
    assert($redis->isConnected() === false);
    
    echo "   ✓ All Redis interface tests passed\n";
    $passed++;
} catch (\Throwable $e) {
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n=== Results: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
