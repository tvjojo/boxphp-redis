<?php
/**
 * RedisPool Redis 连接池
 */
namespace BoxPHP\Redis\Redis;

class RedisPool
{
    /** @var RedisInterface[] */
    protected array $pool = [];

    /** @var array 待使用连接 */
    protected array $available = [];

    /** @var int 池中最大连接数 */
    protected int $maxSize;

    /** @var int 当前已创建连接数 */
    protected int $currentSize = 0;

    /** @var array 连接配置 */
    protected array $config;

    /** @var float 获取连接超时时间(秒) */
    protected float $waitTimeout;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->maxSize = $config['pool_max_size'] ?? 10;
        $this->waitTimeout = $config['pool_wait_timeout'] ?? 3.0;
    }

    /**
     * 获取一个连接
     */
    public function get(): RedisInterface
    {
        // 优先从可用池获取
        while (!empty($this->available)) {
            $conn = array_pop($this->available);
            if ($conn->isConnected()) {
                return $conn;
            }
            $this->currentSize--;
        }

        // 创建新连接
        if ($this->currentSize < $this->maxSize) {
            $conn = $this->createConnection();
            $this->currentSize++;
            return $conn;
        }

        // 等待连接释放
        $start = microtime(true);
        while (microtime(true) - $start < $this->waitTimeout) {
            if (!empty($this->available)) {
                $conn = array_pop($this->available);
                if ($conn->isConnected()) {
                    return $conn;
                }
                $this->currentSize--;
            }
            usleep(10000); // 10ms
        }

        throw new \RuntimeException('Redis pool exhausted, cannot get connection within timeout');
    }

    /**
     * 归还连接
     */
    public function put(RedisInterface $conn): void
    {
        if ($conn->isConnected()) {
            $this->available[] = $conn;
        } else {
            $this->currentSize--;
        }
    }

    /**
     * 创建新连接
     */
    protected function createConnection(): RedisConnection
    {
        $conn = new RedisConnection($this->config);
        $conn->connect(
            $this->config['host'] ?? '127.0.0.1',
            $this->config['port'] ?? 6379,
            $this->config['timeout'] ?? 2.0
        );
        return $conn;
    }

    /**
     * 获取池状态
     */
    public function getStatus(): array
    {
        return [
            'max_size' => $this->maxSize,
            'current_size' => $this->currentSize,
            'available' => count($this->available),
            'in_use' => $this->currentSize - count($this->available),
        ];
    }

    /**
     * 关闭所有连接
     */
    public function destroy(): void
    {
        foreach ($this->pool as $conn) {
            $conn->disconnect();
        }
        foreach ($this->available as $conn) {
            $conn->disconnect();
        }
        $this->pool = [];
        $this->available = [];
        $this->currentSize = 0;
    }
}
