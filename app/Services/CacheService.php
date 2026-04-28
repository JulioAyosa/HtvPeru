<?php
namespace App\Services;

class CacheService {

    private $driver;

    public function __construct(\Core\Cache\CacheInterface $driver = null) {
        if ($driver === null) {
            // Instanciar Redis y probar si está disponible
            $redisDriver = new \Core\Cache\Drivers\RedisCacheDriver();
            if ($redisDriver->isAvailable()) {
                $this->driver = $redisDriver;
            } else {
                // Fallback a archivos si Redis no está disponible o instalado
                $this->driver = new \Core\Cache\Drivers\FileCacheDriver(__DIR__ . '/../../');
            }
        } else {
            $this->driver = $driver;
        }
    }

    /**
     * Set cache para un key determinado
     */
    public function set($key, $data, $ttl = 300) {
        return $this->driver->set($key, $data, $ttl);
    }

    /**
     * Get cache para un key determinado, con un TTL en segundos (default 300)
     */
    public function get($key, $ttl = 300) {
        return $this->driver->get($key, $ttl);
    }

    /**
     * Clear cache explícito
     */
    public function clear($key) {
        return $this->driver->clear($key);
    }
}
