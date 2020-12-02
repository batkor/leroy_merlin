<?php

namespace LeroyMerlin;

use Http\Adapter\Guzzle6\Client;
use Psr\Http\Client\ClientInterface;

/**
 * The base manager.
 *
 * @package LeroyMerlin
 */
abstract class BaseManager {

  /**
   * The http client.
   *
   * @var \Psr\Http\Client\ClientInterface
   */
  private $client;

  /**
   * Set http client.
   *
   * @param \Psr\Http\Client\ClientInterface $client
   *   The http client.
   *
   * @return $this
   */
  public function setClient(ClientInterface $client): self {
    $this->client = $client;
    return $this;
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
