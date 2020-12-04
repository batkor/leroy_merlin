<?php

namespace LeroyMerlin;

use GuzzleHttp\Psr7\Request;
use Http\Adapter\Guzzle6\Client;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * The base request.
 *
 * @package LeroyMerlin
 */
abstract class RequestBase {

  /**
   * The http client.
   *
   * @var \Psr\Http\Client\ClientInterface
   */
  private $client;

  /**
   * Returns request instance..
   *
   * @param string $method
   *   The request method.
   * @param string $uri
   *   The request uri.
   *
   * @return \Psr\Http\Message\RequestInterface
   *   The request instance.
   */
  protected function getRequest(string $method, string $uri): RequestInterface {
    return new Request($method, $uri, ['Content-Type' => 'application/json']);
  }

  /**
   * Returns http client.
   *
   * @return \Psr\Http\Client\ClientInterface
   */
  protected function getClient(): ClientInterface {
    if ($this->client) {
      return $this->client;
    }

    $this->client = new Client();
    return $this->client;
  }

}
