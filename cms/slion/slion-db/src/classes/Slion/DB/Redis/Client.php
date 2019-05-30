<?php

namespace Slion\DB\Redis;

/**
 * Description of Client
 *
 * @author andares
 */
class Client {
    private $config;

    /**
     *
     * @var \Redis
     */
    private $redis;

    /**
     *
     * @var string
     */
    public $error = '';

    public function __construct(array $config) {
        $this->config = $config;
        $this->redis  = new \Redis();
        $this->connect();
    }

    /**
     *
     * @return \Redis
     */
    public function raw(): \Redis {
        return $this->redis;
    }

    /**
     * @todo socket模式下连接参数可能有误
     * @todo 未处理错误
     */
    private function connect() {
        $params = explode(':', $this->config['connect']);
        if (count($params) > 1) {
            $params[] = $this->config['timeout'];
        }
        if ($this->config['persist']) {
            $this->redis->pconnect(...$params);
        } else {
            $this->redis->connect(...$params);
        }
        $this->config['password'] && $this->redis->auth($this->config['password']);
        $this->config['database'] && $this->redis->select($this->config['database']);
    }

    public function __call(string $name, array $arguments) {
        return $this->redis->$name(...$arguments);
    }
}
