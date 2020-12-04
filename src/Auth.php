<?php

namespace LeroyMerlin;

use Http\Message\Authentication;
use LeroyMerlin\Cache\CacheInterface;
use LeroyMerlin\Cache\FileSystemCache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Auth extends RequestBase implements Authentication {

  /**
   * The URI for authorization.
   */
  public const URI_AUTH = 'https://api.leroymerlin.ru/marketplace/user/authbypassword';

  /**
   * The URI for refresh authorization data.
   */
  public const URI_REFRESH = 'https://api.leroymerlin.ru/marketplace/user/updateaccesstoken';

  /**
   * The login.
   *
   * @var string
   */
  private $login;

  /**
   * The password.
   *
   * @var string
   */
  private $password;

  /**
   * The API key.
   *
   * @var string
   */
  private $apiKey;

  /**
   * The refresh token.
   *
   * @var string
   */
  private $refreshToken;

  /**
   * The authorization string.
   *
   * @var string
   */
  private $authString;

  /**
   * The cache store for save access token.
   *
   * @var \LeroyMerlin\Cache\CacheInterface
   */
  private $cacheStore;

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
   * {@inheritdoc}
   */
  public function authenticate(RequestInterface $request) {
    $request = $request
      ->withHeader('apikey', $this->apiKey)
      ->withHeader('Authorization', $this->getAuthString());
    return $request;
  }

  /**
   * Returns authorization string for request header.
   *
   * @return string
   *   The authorization string.
   */
  private function getAuthString() {
    if ($this->authString) {
      return $this->authString;
    }

    $authData = $this->getAccessData();
    $this->authString = "{$authData['token_type']} {$authData['access_token']}";
    return $this->authString;
  }

  /**
   * Returns authorization data from cache or from auth request response.
   *
   * @return array
   *   The associative array containing keys.
   *   - access_token: The access token.
   *   - token_type: The token type.
   *   - expires_in: The time to expire access token.
   *   - refresh_token: The refresh token.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function getAccessData() {
    if ($cacheData = $this->getCache()->get($this->getCacheKey())) {
      $authDataDecode = json_decode($cacheData->data, TRUE);
      // Refresh auth data if expires.
      if ((time() - $cacheData->created) > $authDataDecode['expires_in']) {
        $authData = $this->request($authDataDecode['refresh_token'])->getBody()->getContents();
        $authDataDecode = json_decode($authData, TRUE);
        $this->getCache()->set($this->getCacheKey(), $authData);
        return $authDataDecode;
      }
      return $authDataDecode;
    }

    $authData = $this->request()->getBody()->getContents();
    $authDataDecode = json_decode($authData, TRUE);
    $this->getCache()->set($this->getCacheKey(), $authData);
    return $authDataDecode;
  }

  /**
   * Returns authorisation response.
   *
   * @param string|null $refresh
   *   The refresh token string.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function request(string $refresh = NULL): ResponseInterface {
    if ($refresh) {
      $uri = self::URI_REFRESH;
    }
    else {
      $uri = self::URI_AUTH . '?' . http_build_query([
          'login' => $this->login,
          'password' => $this->password,
        ]);
    }
    $request = $this->getRequest('GET', $uri);
    $request = $request
      ->withHeader('apikey', $this->apiKey)
      ->withHeader('Accept', 'application/json')
      ->withHeader('Content-Type', 'application/json');
    if ($refresh) {
      $request = $request->withHeader('refresh-token', $refresh);
    }

    return $this->getClient()->sendRequest($request);
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
