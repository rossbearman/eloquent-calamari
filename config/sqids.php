<?php

declare(strict_types=1);

return [
    /**
     * The alphabet of all characters a Sqid may be generated from.
     *
     * This will be shuffled for each model using a combination of the model
     * name and the key, unless a fixed alphabet has been specified below.
     *
     * @see https://sqids.org/faq#unique
     */
    'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',

    /**
     * Fixed alphabets to use when encoding and decoding Sqids for a specific model. These will not be shuffled.
     *
     * It is highly recommended to set these in production, to ensure changes to the model name or key do not
     * invalidate Sqids that are actively being used.
     */
    'alphabets' => [
        //App\Models\Customer::class => env('SQIDS_CUSTOMER_ALPHABET'),
    ],

    /**
     * The default minimum length for Sqids.
     *
     * @see https://sqids.org/faq#minlength
     */
    'min_length' => 10,

    /**
     * The minimum length for Sqids generated for a specific model.
     */
    'min_lengths' => [
        //App\Models\Customer::class => 6,
    ],

    /**
     * A key used to seed the alphabet randomizer, when combined with the model name. The combined key will
     * be truncated to 32 bytes, or padded by repeating elements of the key to reach 32 bytes.
     *
     * The Laravel application key is used by default, as this will typically not change in production.
     */
    'key' => env('APP_KEY'),

    /**
     * Add additional words to the Sqids blocklist. Changing this may invalidate previously generated Sqids.
     *
     * @see https://github.com/sqids/sqids-php/blob/main/src/Sqids.php#L24
     */
    'blocklist' => [],

    /**
     * Setting this to `false` for a model will disable collision detection, allowing non-canonical Sqids to
     * be used to access entities of the specified model. This is generally not recommended.
     *
     * `true` is the default for all models and does not need to be specified
     */
    'canonical_checks' => [
        //App\Models\Customer::class => false,
    ],
];
