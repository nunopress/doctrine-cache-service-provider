# Doctrine Cache Service Provider

The Doctrine Cache Service Provider add [Doctrine Cache](http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html) package for [Silex Microframework](http://silex.sensiolabs.org/) or every [Pimple Container](http://pimple.sensiolabs.org/) project's.

### Cache Drivers

Doctrine Cache have some cache drivers, for a complete list and updated you can check [here](https://github.com/doctrine/cache/tree/master/lib/Doctrine/Common/Cache).

### Parameters

#### cache.profiles

In this array you can configure every parameters for the Service Provider.

For more informations about every cache driver options check below in [this section](#cache-driver-options).

### Services

Here the list of every services used in this Service Provider.

#### cache

The Doctrine Cache driver implemented with `Doctrine\Common\Cache\Cache`. The main way to interact with the Service Provider.

> This service use the **FIRST** profile available so take attention when you register the profiles for the order how you register.

#### cache.profiles.initializer

This service initialize the default profile for the Service Provider.

#### cache.stores

This service use multiple profiles (_example for use different servers for different environments_), you can manage with this example:

```php
// Register the service provider with multiple connections
$app->register(new \NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'cache.profiles' => [
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

// Access to different profiles
$app['cache.stores']['local']->fetch('cache_key');
// or with DoctrineCacheTrait
$app->cache('local')->fetch('cache_key');
```

#### cache.store.filesystem

Return the `Doctrine\Common\Cache\FilesystemCache` object.

#### cache.store.array

Return the `Doctrine\Common\Cache\ArrayCache` object.

#### cache.store.apcu

Return the `Doctrine\Common\Cache\ApcuCache` object.

#### cache.store.mongodb

Return the `Doctrine\Common\Cache\MongoDBCache` object.

#### cache.mongodb.connector

return the `MongoCollection` object after connected to MongoDB server.

#### cache.store.redis

Return the `Doctrine\Common\Cache\RedisCache` object.

#### cache.redis.connector

Return the `Redis` object after connected to Redis server.

#### cache.store.xcache

Return the `Doctrine\Common\Cache\XcacheCache` object.

#### cache.store.chain

Return the `Doctrine\Common\Cache\ChainCache` object.

#### cache.store.memcache

Return the `Doctrine\Common\Cache\MemcacheCache` object.

#### cache.memcache.connector

Return the `Memcache` object after connected to Memcache server.

#### cache.store.memcached

Return the `Doctrine\Common\Cache\MemcachedCache` object.

#### cache.memcached.connector

Return the `Memcached` object after connected to Memcached server.

#### cache.store.couchbase

Return the `Doctrine\Common\Cache\CouchbaseCache` object.

#### cache.couchbase.connector (need testing)

Return the `Couchbase` object after connected to Couchbase server.

#### cache.store.phpfile

Return the `Doctrine\Common\Cache\PhpFileCache` object.

#### cache.store.predis

Return the `Doctrine\Common\Cache\PredisCache` object.

#### cache.predis.connector (need testing)

Return the `Predis\Client` object after connected to Predis server.

#### cache.store.riak

Return the `Doctrine\Common\Cache\RiakCache` object.

#### cache.riak.connector (need testing)

Return the `Riak\Bucket` object after connected to Riak server.

#### cache.store.sqlite3

Return the `Doctrine\Common\Cache\Sqlite3Cache` object.

#### cache.sqlite3.connector

Return the `Sqlite3` object after connected to Sqlite3.

#### cache.store.void

Return the `Doctrine\Common\Cache\VoidCache` object.

#### cache.store.wincache

Return the `Doctrine\Common\Cache\WinCacheCache` object.

#### cache.store.zenddata

Return the `Doctrine\Common\Cache\ZendDataCache` object.

#### cache.store.pdo

Return the `NunoPress\Doctrine\Common\Cache\PDOCache` object.

#### cache.pdo.connector

Return the `PDO` object after connected to PDO server.

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

> Not all connectors are tested, so please be careful and send any issue with that.

### Registering

```php
$app->register(new NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'cache.profiles' => [
        'default' => [
            'driver' => 'array'
        ]
    ]
]);
```

If you need more connections you can define more arrays following this example:

```php
$app->register(new NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'cache.profiles' => [
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
    'cache.profiles' => [
        'default' => [
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

#### cache

```php
// More readable with DoctrineCacheTrait: $app->cache('profile_name')->contains('cache_key');
$app['cache.stores']['profile_name']->contains('cache_key');
```

### Customization

#### DoctrineCacheTrait

Our developers used a personal vision for the `cache` method, this our implementation:

```php
namespace App\Traits;

/**
 * Class DoctrineCacheTrait
 * @package App\Traits
 */
trait DoctrineCacheTrait
{
    use \NunoPress\Silex\Application\DoctrineCacheTrait;

    /**
     * @param null $profile
     * @return \Doctrine\Common\Cache\Cache
     */
    public function cache($profile = null)
    {
        $profile = $profile ?: $this['environment'];

        return $this['cache.stores'][$profile];
    }
}
```

With this implementation we can use in our code `$app->cache()->get('cache_key')` and in `development` environment we use the `VoidCache` and in `production` we use another caching profile.

> This section still in development because we need to rewrite all the configuration system for manage the driver options.
