<?php
namespace Core\Cache;

interface CacheInterface {
    public function set($key, $data, $ttl = 300);
    public function get($key, $ttl = 300);
    public function clear($key);
}
