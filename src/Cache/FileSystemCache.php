<?php

namespace LeroyMerlin\Cache;

/**
 * Implements file system cache.
 */
class FileSystemCache implements CacheInterface {

  /**
   * {@inheritdoc}
   */
  public function get(string $key) {
    $filename = $this->file($key);
    if (!\file_exists($filename)) {
      return null;

    }

    $c = new \stdClass();
    $c->data = \file_get_contents($filename);
    $c->created = \filemtime($filename);
    return $c;
  }

  /**
   * {@inheritdoc}
   */
  public function set(string $key, $data): void {
    file_put_contents($this->file($key), $data);
  }

  public function clear(string $key): void {
    unlink($this->file($key));
  }

  /*
   * Returns path to file for save data in file system.
   */
  private function file(string $key): string {
    return \sys_get_temp_dir() . '/' . $key;
  }

}
