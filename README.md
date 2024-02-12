# Eloquent Calamari

[![Latest Stable Version](https://poser.pugx.org/rossbearman/eloquent-calamari/v/stable?style=flat-square)](https://packagist.org/packages/rossbearman/eloquent-calamari)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Eloquent Calamari integrates the [Sqids](https://sqids.org/php) algorithm into Laravel and Eloquent, enabling you to seamlessly use obfuscated, unique IDs in place of your internal auto-incrementing IDs.

- Unique Sqids for every ID on every model
- Transparently handle non-canonical IDs

## Getting Started

Require this package with [Composer](https://getcomposer.org/).

`composer require rossbearman/sqids`

Add the `HasSqids` and `SqidBasedRouting` traits to your models.

```php
use RossBearman\Sqids\Concerns\HasSqids;
use RossBearman\Sqids\Concerns\SqidBasedRouting;

class Customer extends Model
{
    use HasSqids, SqidBasedRouting;
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

$customer->id // 1
$customer->sqid // 3irWXI2rFV
```

`example.com/customer/3irWXI2rFV` now returns the Customer details.

Common query methods are also available.

```php
Customer::findBySqid($sqid);

Customer::findBySqidOrFail($sqid)

Customer::whereSqid($sqid)->get();
```

## Configuration

By default, Eloquent Calamari generates a random [alphabet](https://sqids.org/faq#unique) for each model, by shuffling the default alphabet with the [Xoshiro256StarStar](https://www.php.net/manual/en/class.random-engine-xoshiro256starstar.php) algorithm, seeded with a combination of the name of the model and the key set in the config. 

This ensures that entities of different models with the same ID will have a unique Sqid, however it is fragile to the model name or app key being changed. If either of these are changed, Sqids will no longer resolve back to the same ID.

**It is highly recommended** that you explicitly set a pre-shuffled alphabet for each model using the `sqids.alphabets` config key, which will disable the shuffling behaviour for that model.

### Setting alphabets
Start by publishing the `sqids.php` config file to your `config` directory. 

```shell
php artisan vendor:publish --provider="RossBearman\Sqids\SqidsServiceProvider"
```

Then generate a new alphabet for your  model.

```shell
php artisan sqids:alphabet App\Models\Customer
```

You can also generate alphabets for multiple models at once.

```shell
php artisan sqids:alphabet App\Models\Customer App\Models\Order App\Models\Invoice
```

Follow the instructions provided by the command to add the new keys to your config and `.env` file.

### Confirm alphabet is being used

To ensure that Eloquent Calamari is using the expected alphabet for a specific model, use the following command.

```shell
php artisan sqids:check
```

This command will list all the models that have successfully been registered in the config and whether or not the class string can be resolve to a class in the application.

### Setting minimum Sqid lengths

By default, all Sqids will be a minimum of 10 characters. You can adjust this for each model by assigning different values (to a minimum of 3) to the `sqids.min_lengths` config array. 

```php
    'min_lengths' => [
        App\Model\Customer::class => 20,
        App\Model\Order::class => 8,
        App\Model\Invoice::class => 30,
    ],
```

## Sqid Collisions
By design, [multiple Sqids can resolve to the same number](https://sqids.org/faq#collisions), however Eloquent Calamari will always return the same Sqid for a given number. Furthermore, this is the only Sqid that can be used to access an entity, and any other Sqid that would normally resolve to the same number will be rejected.

This check can be disabled on a per-model basis by adding an entry to the 


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
