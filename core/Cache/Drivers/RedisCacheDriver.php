<?php
namespace Core\Cache\Drivers;

use Core\Cache\CacheInterface;

class RedisCacheDriver implements CacheInterface {
    private $redis;
    private $prefix;

    public function __construct($host = '127.0.0.1', $port = 6379, $prefix = 'pn_') {
        $this->prefix = $prefix;
        
        // Asume que la extensión Redis de PHP está instalada.
        // Si no está instalada, el servicio debería hacer fallback a FileCacheDriver
        if (class_exists('Redis')) {
            $this->redis = new \Redis();
            try {
                $this->redis->connect($host, $port);
            } catch (\Exception $e) {
                $this->redis = null;
            }
        }
    }

    public function isAvailable() {
        return $this->redis !== null;
    }

    public function set($key, $data, $ttl = 300) {
        if (!$this->isAvailable()) return false;
        
        $value = json_encode($data);
        if ($ttl > 0) {
            return $this->redis->setex($this->prefix . $key, $ttl, $value);
        } else {
            return $this->redis->set($this->prefix . $key, $value);
        }
    }

    public function get($key, $ttl = 300) {
        // En Redis el TTL se maneja al guardar, el parámetro $ttl aquí es por compatibilidad de interfaz
        if (!$this->isAvailable()) return false;
        
        $value = $this->redis->get($this->prefix . $key);
        if ($value !== false) {
            return json_decode($value, true);
        }
        return false;
    }

    public function clear($key) {
        if (!$this->isAvailable()) return false;
        
        return $this->redis->del($this->prefix . $key) > 0;
    }
}
