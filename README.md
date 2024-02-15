# Eloquent Calamari

[![Latest Stable Version](https://poser.pugx.org/rossbearman/eloquent-calamari/v/stable?style=flat-square)](https://packagist.org/packages/rossbearman/eloquent-calamari)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Eloquent Calamari integrates the [Sqids](https://sqids.org/php)[^1] algorithm into Laravel and Eloquent, enabling you to seamlessly use obfuscated, unique IDs in place of your internal auto-incrementing IDs.

- Obfuscate auto-incrementing IDs with short, unique identifiers
- Unique Sqids across models, ID 1 will be represented differently on every model
- Optional route model binding with `SqidBasedRouting`
- Transparently handle non-canonical IDs

`example.com/link/2fC37YMkO` === `Link::find(1)`

`example.com/video/TaRfL1RAK` === `Video::find(1)`

## Getting Started

Require this package with [Composer](https://getcomposer.org/).

`composer require rossbearman/eloquent-calamari`

Add the `HasSqid` and `SqidBasedRouting` traits to your models.

```php
use RossBearman\Sqids\HasSqids;
use RossBearman\Sqids\SqidBasedRouting;

class Customer extends Model
{
    use HasSqid, SqidBasedRouting;
}
```

Create a route for your model.
```php
Route::get('/customer/{customer}', function (Customer $customer) {
    return $customer;
});
```

```php
$customer = Customer::create(['name' => 'Squidward']);

$customer->id; // 1
$customer->sqid; // 3irWXI2rFV
```

`example.com/customer/3irWXI2rFV` now returns the Customer details.

### Querying
Common query methods are also available.

```php
Customer::findBySqid($sqid);

Customer::findBySqidOrFail($sqid);

Customer::whereSqid($sqid)->get();

Customer::whereSqidIn($sqids)->get();

Customer::whereSqidNotIn($sqids)->get();
```

### Representation

By default the Sqid is not included in the model's `toArray()` or `toJson()` output and the ID is not hidden from these. You can use Eloquent's `appends` and `hidden` properties to achieve this.

```php
class Customer extends Model
{
    use HasSqid, SqidBasedRouting;
    
    protected $appends = ['sqid'];
    protected $hidden = ['id'];
}
```

### Custom Routing
You can take advantage of `SqidBasedRouting` while still having a different default binding by overriding the model's `getRouteKeyName()` method.
```php
class Customer extends Model
{
    use HasSqid, SqidBasedRouting;
    
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
```

```php
// Routes by ID by default
Route::get('/admin/{customer}', function (Customer $customer) {
    return $customer;
});

// Routes by Sqid when specified
Route::get('/customer/{customer:sqid}', function (Customer $customer) {
    return $customer;
});
```


## Configuration

By default, Eloquent Calamari generates a random [alphabet](https://sqids.org/faq#unique) for each model, by shuffling the default alphabet with the [Xoshiro256StarStar](https://www.php.net/manual/en/class.random-engine-xoshiro256starstar.php) algorithm, seeded with a combination of the name of the model and the key set in the config. 

This ensures that entities of different models with the same ID will have a unique Sqid, however it is fragile to the model name or app key being changed. If either of these are changed, Sqids will no longer resolve back to the same ID.

> [!IMPORTANT]
> **It is highly recommended** that you explicitly set a pre-shuffled alphabet for each model using the `sqids.alphabets` config key, which will disable the shuffling behaviour for that model.

### Setting alphabets
Start by publishing the `sqids.php` config file to your `config` directory. 

```shell
php artisan vendor:publish --provider="RossBearman\Sqids\SqidsServiceProvider"
```

Then generate a new alphabet for your model.

```shell
php artisan sqids:alphabet "App\Models\Customer"
```

You can also generate alphabets for multiple models at once.

```shell
php artisan sqids:alphabet "App\Models\Customer" "App\Models\Order" "App\Models\Invoice"
```

Follow the instructions provided by the command to add the new keys to your `config/sqids.php` and `.env` files.

### Confirm alphabet is being used

To ensure that Eloquent Calamari is using the expected alphabet for a specific model, use the following command.

```shell
php artisan sqids:check
```

This will list all the models that have successfully been registered in the config and whether the class string can be resolved to a class in your application.

### Setting minimum Sqid lengths

By default, all Sqids will be a minimum of 10 characters. You can adjust this for each model by assigning different values (to a minimum of 3) to the `sqids.min_lengths` config array. 

```php
    'min_lengths' => [
        App\Model\Customer::class => 20,
        App\Model\Order::class => 8,
        App\Model\Invoice::class => 30,
    ],
```

The maximum length of a Sqid is dependent on the input ID and the alphabet used. A more varied alphabet (upper and lower case letters, numbers and symbols) will result in shorter Sqids.

### Canonical Sqids
By design, [multiple Sqids can resolve to the same number](https://sqids.org/faq#collisions), however Eloquent Calamari will always return the same Sqid for a given number. Furthermore, this is the only Sqid that can be used to access an entity, and any other Sqid that would normally resolve to the same number will be rejected.

This check can be disabled on a per-model basis by adding an entry to the `sqids.canonical_checks` config array.

```php
    'canonical_checks' => [
        App\Model\Customer::class => false,
    ],
```

## Development
PHPUnit tests:

```shell
composer test
```

PHPStan analysis:

```shell
composer analyse
```

Pint linting:

```shell
composer lint
```

## Security

Please email Ross Bearman <ross@rossbearman.co.uk> if you have discovered a vulnerability in this package.

## License

MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

[^1]: Sqids is the latest version of the Hashids algorithm, redesigned to accomodate custom blocklists and a better encoding scheme.
