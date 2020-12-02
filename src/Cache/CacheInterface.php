<?php

namespace LeroyMerlin\Cache;

/**
 * Interface for implement cache classes.
 */
interface CacheInterface {

  /**
   * Returns data from cache by key.
   *
   * @param string $key
   *   The key data in cache.
   *
   * @return \stdClass|null
   *   The object contains properties.
   *   - data: The cache data.
   *   = created: The created time in Unix.
   */
  public function get(string $key);

  /**
   * Save data to cache.
   *
   * @param string $key
   *   The cache key.
   * @param mixed $data
   *   The data save to cache.
   */
  public function set(string $key, $data): void;

  public function clear(string $key): void;

}
