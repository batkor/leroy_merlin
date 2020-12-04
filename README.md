### The PSR-7 http client for Leroy Merlin store

Example

```php
use LeroyMerlin\Auth;
use LeroyMerlin\V1\Products;

$auth = new Auth('login', 'password', 'apiKey');
$manager = new Products($auth);
// Get assortments list.
$assortments = $manager->assortment();
// Update price.
$manager->updatePrice([
  [
    'marketplaceId' => '111111',
    'price' => 1111,
  ]
]);
// Update stock.
$manager->updateStoke([
  [
    'marketplaceId' => '111111',
    'stock' => 11,
  ]
]);
```
