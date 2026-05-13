<?php
namespace Core\Cache\Drivers;

use Core\Cache\CacheInterface;

class FileCacheDriver implements CacheInterface {
    private $cache_dir;

    public function __construct($cache_dir = null) {
        $this->cache_dir = $cache_dir ?: __DIR__ . '/../../../storage/cache/'; 
    }

    public function set($key, $data, $ttl = 300) {
        $file = rtrim($this->cache_dir, '/') . '/' . $key . '_cache.json';
        return file_put_contents($file, json_encode($data));
    }

    public function get($key, $ttl = 300) {
        $file = rtrim($this->cache_dir, '/') . '/' . $key . '_cache.json';
        if (file_exists($file)) {
            if ((time() - filemtime($file)) < $ttl) {
                return json_decode(file_get_contents($file), true);
            }
        }
        return false;
    }

    public function clear($key) {
        $file = rtrim($this->cache_dir, '/') . '/' . $key . '_cache.json';
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    public function clearAll() {
        $files = glob(rtrim($this->cache_dir, '/') . '/*_cache.json');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        return true;
    }
}
