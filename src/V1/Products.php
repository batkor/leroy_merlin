<?php

namespace LeroyMerlin\V1;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;
use LeroyMerlin\Auth;
use LeroyMerlin\BaseManager;
use LeroyMerlin\Exception\LeroyMerlinException;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\stream_for;

final class Products extends BaseManager {

  /**
   * The URI for get assortments list.
   */
  public const URI_ASSORTMENT = 'https://api.leroymerlin.ru/marketplace/api/v1/products/assortment';

  /**
   * The URI for update stock.
   */
  public const URI_STOCK = 'https://api.leroymerlin.ru/marketplace/api/v1/products/stock';

  /**
   * The URI for update price.
   */
  public const URI_PRICE = 'https://api.leroymerlin.ru/marketplace/api/v1/products/price';

  /**
   * @var \LeroyMerlin\AuthOld
   */
  private $auth;

  /**
   * Products constructor.
   *
   * @param \LeroyMerlin\Auth $auth
   *   The authorization object.
   */
  public function __construct(Auth $auth) {
    $this->auth = $auth;
  }

  /**
   * Returns assortments list.
   *
   * @return array
   *   The assortments list and  every item containing keys.
   *   - productId: The product ID in your system.
   *   - marketplaceId: The product ID in Leroy Merlin system.
   *   - price: The actual price.
   *   - stock: The actual quantity.
   *   - removedFromSale: The product status.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function assortment(): array {
    $request = new Request('GET', self::URI_ASSORTMENT, ['Content-Type' => 'application/json']);
    $request = $this->auth->authenticate($request);
    $response = $this->getClient()->sendRequest($request);
    $data = $response->getBody()->getContents();
    $decodeData = json_decode($data, TRUE);
    return $decodeData['result']['products'];
  }

  /**
   * Update stock.
   *
   * @param array $products
   *   The array items containing keys.
   *   - marketplaceId: The product ID in Leroy Merlin system.
   *   - stock: The actual quantity.
   */
  public function updateStoke(array $products): void {
    $this->updateRequest(self::URI_STOCK, stream_for(json_encode($this->prepareBody($products))));

  }

  /**
   * Update price.
   *
   * @param array $products
   *   The array items containing keys.
   *   - marketplaceId: The product ID in Leroy Merlin system.
   *   - price: The actual price.
   */
  public function updatePrice(array $products): void {
    $this->updateRequest(self::URI_PRICE, stream_for(json_encode($this->prepareBody($products))));
  }


  /**
   * Send update request.
   *
   * @param string $uri
   *   The uri for request.
   * @param \Psr\Http\Message\StreamInterface $stream
   *   The body stream.
   *
   * @throws \LeroyMerlin\Exception\LeroyMerlinException
   * @throws \Psr\Http\Client\ClientExceptionInterface
   */
  private function updateRequest(string $uri, StreamInterface $stream): void {
    $request = new Request('POST', $uri, ['Content-Type' => 'application/json']);
    $request = $this->auth
      ->authenticate($request)
      ->withBody($stream);
    $response = $this->getClient()->sendRequest($request);
    $data = $response->getBody()->getContents();
    $decodeData = json_decode($data, TRUE);
    if ($response->getStatusCode() !== 200) {
      throw new LeroyMerlinException($response->getReasonPhrase(), $decodeData);
    }
  }

  /**
   * Prepare body before request.
   *
   * @param $body
   *   The s body for prepare.
   *
   * @return array[]
   *   The body for request.
   */
  private function prepareBody($body) {
    return ['data' => ['products' => $body]];
  }

}
