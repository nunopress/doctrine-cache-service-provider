# Doctrine Cache Service Provider

The Doctrine Cache Service Provider add [Doctrine Cache](http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html) package for [Silex Microframework](http://silex.sensiolabs.org/) or every [Pimple Container](http://pimple.sensiolabs.org/) project's.

### Cache Drivers

Doctrine Cache have some cache drivers, for a complete list and updated you can check [here](https://github.com/doctrine/cache/tree/master/lib/Doctrine/Common/Cache).

### Parameters

#### cache.options

In this array you can configure every parameters for the Service Provider.

For more informations about every cache driver options check below in [this section](#cache-driver-options).

### Services

Here the list of every services used in this Service Provider.

#### cache

The Doctrine Cache driver implemented with `Doctrine\Common\Cache\Cache`. The main way to interact with the Service Provider.

#### cache.options.initializer

This service initialize the default options for the Service Provider.

#### caches

This service give the possible to use multiple connections (_example for use different servers for different environments_), you can manage with this example:

```php
// Register the service provider with multiple connections
$app->register(new \NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'caches.options' => [
        'local' => [
            'driver' => 'xcache'
        ],
        
        'remote' => [
            'driver' => 'array',
            'namespace' => 'test'
        ],
        
        'test' => [
            'driver' => 'filesystem',
            'parameters' => [
                'directory' => '/cache'
            ]
        ]
    ]
]);

// Access to different connections
$app['caches']['local']->fetch('cache_key');
// or with DoctrineCacheTrait
$app->caches('local')->fetch('cache_key');
```

#### cache.filesystem

Return the `Doctrine\Common\Cache\FilesystemCache` object.

#### cache.array

Return the `Doctrine\Common\Cache\ArrayCache` object.

#### cache.apcu

Return the `Doctrine\Common\Cache\ApcuCache` object.

#### cache.mongodb

Return the `Doctrine\Common\Cache\MongoDBCache` object.

#### cache.redis

Return the `Doctrine\Common\Cache\RedisCache` object.

#### cache.xcache

Return the `Doctrine\Common\Cache\XcacheCache` object.

#### cache.chain

Return the `Doctrine\Common\Cache\ChainCache` object.

#### cache.memcache

Return the `Doctrine\Common\Cache\MemcacheCache` object.

#### cache.memcached

Return the `Doctrine\Common\Cache\MemcachedCache` object.

#### cache.couchbase *

Return the `Doctrine\Common\Cache\CouchbaseCache` object.

#### cache.phpfile

Return the `Doctrine\Common\Cache\PhpFileCache` object.

#### cache.predis *

Return the `Doctrine\Common\Cache\PredisCache` object.

#### cache.riak *

Return the `Doctrine\Common\Cache\RiakCache` object.

#### cache.sqlite3

Return the `Doctrine\Common\Cache\Sqlite3Cache` object.

#### cache.void

Return the `Doctrine\Common\Cache\VoidCache` object.

#### cache.wincache

Return the `Doctrine\Common\Cache\WinCacheCache` object.

#### cache.zenddata

Return the `Doctrine\Common\Cache\ZendDataCache` object.

#### cache.pdo

Return the `NunoPress\Doctrine\Common\Cache\PDOCache` object.

#### cache.factory

This service used to choice the right cache driver.

#### cache.default_options

Simple array with defined the default options. This the default options:

```php
$app['cache.default_options'] = [
    'driver' => 'array',
    'namespace' => null,
    'parameters' => []
];
```

> The services suffixed with ***** need to be testing.

### Registering

```php
$app->register(new NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'cache.options' => [
        'driver' => 'array'
    ]
]);
```

If you need more connections you can define with `caches.options` array following this example:

```php
$app->register(new NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'caches.options' => [
        'default' => [
            'driver' => 'array'
        ],
        
        'local' => [
            'driver' => 'filesystem',
            'parameters' => [
                'cache_dir' => '/cache'
            ]
        ]
    ]
]);
```

### Cache Driver Options

Now the list of required parameters for every cache driver:

#### filesystem

##### cache_dir

Directory where the Service Provider save the cache.

#### array

No configuration.

#### apcu

No Configuration.

#### mongodb

##### server

MongoDB server address.

##### name

MongoDB name.

##### collection

MongoDB collection.

#### Redis

##### host

Redis server address.

##### port

Redis server port.

##### password (optional)

Password for access to Redis server.

#### xcache

No configuration.

#### chain

Register the cache drivers to use the Chain Cache, example:

```php
$app->register(new NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'cache.options' => [
        'driver' => 'chain',
        'parameters' => [
            [
                'driver' => 'filesystem',
                'parameters' => [
                    'cache_dir' => __DIR__ . '/../cache'
                ]
            ],
            [
                'driver' => 'array'
            ]
        ]
    ]
]);
```

> This system to configure the Service Provider is under development, some modifications can change in the next release.

#### memcache

##### host

Memcache server address.

##### port

Memcache server port.

#### memcached

##### host

Memcached server address.

##### port

Memcached server port.

#### couchbase

##### host

Couchbase server address.

##### port

Couchbase server port.

##### username (optional)

Username for access to Couchbase server.

##### password (optional)

Password for access to Couchbase server.

##### bucket (optional)

Bucket name (_default is the default name for the bucket_).

#### phpfile

##### directory

Directory where the cache files are saved.

##### extension (optional)

Extension for the cache files.

##### umask (optional)

Umask for the cache files.

#### predis

##### scheme

Use `tcp` or `socket` for the Predis connection.

##### host

Predis server address.

##### port

Predis server port.

##### path

Use this instead of `host` and `port` for `socket` scheme.

#### riak

##### host

Riak server address.

##### port

Riak server port.

##### bucket

Riak bucket name.

#### sqlite3

##### filename

Sqlite3 filename complete with path.

##### table

Sqlite3 table name.

##### flags (optional)

Sqlite3 flags options.

##### encryption_key (optional)

Sqlite3 encryption key.

#### void

No configuration.

#### wincache

No configuration.

#### zenddata

No configuration.

#### pdo

##### dns

For more information's about the format [see here](http://php.net/manual/en/pdo.drivers.php).

##### username (optional)

Username for connect to PDO.

##### password (optional)

Password for connect to PDO.

##### options (optional)

Options for connect to PDO.

### Usage

The Config provider provides a `config` service:

```
// Read cache
$app['cache']->fetch('cache_key');

// Check cache
$app['cache']->contains('cache_key');

// Save cache
$app['cache']->save('cache_key', 'cache_value', 100); // the third param is a lifetime in seconds.

// Delete cache
$app['cache']->delete('cache_key');
```

> Read the [reference](http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html#cache-drivers) for all methods available.

### Traits

Define this trait in your `Application` class:

```php
class App extends \Silex\Application
{
    use \NunoPress\Silex\Application\DoctrineCacheTrait;
}

$app = new App();

$name = $app->readCache('cache_key');
```

`NunoPress\Silex\Application\DoctrineCacheTrait` adds the following shortcuts:

#### readCache

```php
// Read cache
$app['cache']->fetch('cache_key');
```

#### containsCache

```php
// Check cache
$app['cache']->contains('cache_key');
```

#### saveCache

```php
// Save cache
$app['cache']->save('cache_key', 'cache_value', 100); // the third param is a lifetime in seconds.
```

#### deleteCache

```php
// Delete cache
$app['cache']->delete('cache_key');
```

#### caches

```php
// Chain cache with DoctrineCacheTrai: $app->caches('connection_name')->contains('cache_key');
$app['caches']['connection_name']->contains('cache_key');
```

### Customization

> This section still in development because we need to rewrite all the configuration system for manage the driver options.
