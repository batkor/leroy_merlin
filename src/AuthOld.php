<?php declare(strict_types=1);

namespace LeroyMerlin;

use GuzzleHttp\Client;
use LeroyMerlin\Cache\CacheInterface;
use LeroyMerlin\Cache\FileSystemCache;

class AuthOld {

  /**
   * The URI for authorization.
   */
  public const URI_AUTH = 'https://api.leroymerlin.ru/marketplace/user/authbypassword';

  /**
   * The URI for refresh authorization data.
   */
  public const URI_REFRESH = 'https://api.leroymerlin.ru/marketplace/user/updateaccesstoken';

  /**
   * The login for authorization.
   *
   * @var string
   */
  private $login;

  /**
   * The password for authorization.
   *
   * @var string
   */
  private $password;

  /**
   * The API key for authorization.
   *
   * @var string
   */
  private $apiKey;

  /**
   * The cache store for save access token.
   *
   * @var \LeroyMerlin\Cache\CacheInterface
   */
  private $cacheStore;

  /**
   * The static request data.
   *
   * @var \stdClass
   */
  private $data;

  /**
   * The key name in cache store.
   *
   * @var string
   */
  private $cacheKey;

  /**
   * Auth constructor.
   *
   * @param string $login
   *   The login for authorization.
   * @param string $password
   *   The password for authorization.
   * @param string $apiKey
   *   The API key for authorization.
   */
  public function __construct(string $login, string $password, string $apiKey) {
    $this->login = $login;
    $this->password = $password;
    $this->apiKey = $apiKey;
  }

  /**
   * Create new instance by array.
   *
   * @param array $config
   *   Associate array contains.
   *   - login: The login for authorization.
   *   - pass: The password for authorization.
   *   - apiKey: The API key for authorization.
   *
   * @return self
   *   Instance current object.
   */
  public static function byArray(array $config): self {
    return new static($config['login'], $config['pass'], $config['apiKey']);
  }

  /**
   * Returns access token.
   *
   * @return string
   *   The access token.
   */
  public function accessToken(): string {
    if ((time() - $this->getData()->created) > $this->getData()->expires_in) {
      return $this->refreshData()->access_token;
    }
    return $this->getData()->access_token;
  }

  /**
   * Returns data after refresh.
   *
   * @return \stdClass
   *   The updated data.
   */
  private function refreshData(): \stdClass {
    $client = new Client();
    $options = $this->options();
    unset($options['query']);
    $options['headers']['refresh-token'] = $this->getData()->refresh_token;

    $response = $client->get(self::URI_REFRESH, $options);
    $data = $response->getBody()->getContents();
    $decodeData = json_decode($data);
    if (isset($decodeData->error)) {
      throw new \Exception($decodeData->error_description);
    }
    $this->getCache()->set($this->getCacheKey(), $data);
    // Reset data in static cache.
    $this->data = NULL;

    return $this->getData();
  }

  /**
   * Returns request data.
   *
   * @return \stdClass
   *   The data object containing keys.
   *   - access_token: The access token.
   *   - token_type: The token type.
   *   - expires_in: The time to expire access token.
   *   - refresh_token: The refresh token.
   */
  private function getData() {

    // Fast return data from static cache.
    if ($this->data) {
      return $this->data;
    }

    // Returns data from cache.
    if ($cacheData = $this->getCache()->get($this->getCacheKey())) {
      $this->data = $cacheData;
      return $this->data;
    }

    $client = new Client();
    $response = $client->get(self::URI_AUTH, $this->options());
    $data = $response->getBody()->getContents();
    $decodeData = json_decode($data);
    if (isset($decodeData->error)) {
      throw new \Exception($decodeData->error_description);
    }
    $this->getCache()->set($this->getCacheKey(), $data);
    $this->data = $decodeData;
    $this->data->created = time();
    return $this->data;
  }

  /**
   * Set cache store.
   *
   * @param \LeroyMerlin\Cache\CacheInterface $cache
   *   The cache object.
   *
   * @return $this
   *   Current object.
   */
  public function setCache(CacheInterface $cache): self {
    $this->cacheStore = $cache;
    return $this;
  }

  /**
   * Returns cache store.
   *
   * @return \LeroyMerlin\Cache\CacheInterface
   *   The cache store.
   */
  private function getCache(): CacheInterface {
    if ($this->cacheStore) {
      return $this->cacheStore;
    }
    $this->cacheStore = new FileSystemCache();
    return $this->cacheStore;
  }

  /**
   * Returns request options.
   *
   * @return array[]
   *   The request options.
   */
  private function options(): array {
    return [
      'headers' => [
        'apikey' => $this->apiKey,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ],
      'query' => [
        'login' => $this->login,
        'password' => $this->password,
      ],
    ];
  }

  public function getApiKey() {
    return $this->apiKey;
  }

  /**
   * Returns key for save data to cache.
   *
   * @return string
   *   The key in cache.
   */
  private function getCacheKey(): string {
    if ($this->cacheKey) {
      return $this->cacheKey;
    }
    $this->cacheKey = \md5($this->login . $this->password);
    return $this->cacheKey;
  }

}
