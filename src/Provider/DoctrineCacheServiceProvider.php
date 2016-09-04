<?php

namespace NunoPress\Silex\Provider;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\CouchbaseCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\MongoDBCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\PredisCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\RiakCache;
use Doctrine\Common\Cache\SQLite3Cache;
use Doctrine\Common\Cache\VoidCache;
use Doctrine\Common\Cache\WinCacheCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\Common\Cache\ZendDataCache;
use NunoPress\Doctrine\Common\Cache\PDOCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;


/**
 * Class DoctrineCacheServiceProvider
 * @package NunoPress\Silex\Provider
 */
class DoctrineCacheServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app['cache.profiles.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if (true === $initialized) {
                return;
            }

            $initialized = true;

            if (false === isset($app['cache.profiles'])) {
                $app['cache.profiles'] = [
                    'default' => $app['cache.default_options']
                ];
            }
        });

        $app['cache.stores'] = function (Container $app) {
            $app['cache.profiles.initializer']();

            $container = new Container();
            foreach ($app['cache.profiles'] as $name => $options) {
                // Merge with default options
                $options = array_merge($app['cache.default_options'], $options);

                $container[$name] = function () use ($app, $options) {
                    /** @var \Doctrine\Common\Cache\CacheProvider $cache */
                    $cache = $app['cache.factory']($options['driver'], $options['parameters']);
                    $cache->setNamespace($options['namespace']);

                    return $cache;
                };
            }

            return $container;
        };

        $app['cache.factory'] = $app->protect(function ($driver, $options) use ($app) {
            switch ($driver) {
                case 'array':
                    return $this->createArrayCache($app);
                    break;
                case 'apcu':
                    return $this->createApcuCache($app);
                    break;
                case 'redis':
                    return $this->createRedisCache($app, $options);
                    break;
                case 'xcache':
                    return $this->createXcacheCache($app);
                    break;
                case 'mongodb':
                    return $this->createMongoDBCache($app, $options);
                    break;
                case 'filesystem':
                    return $this->createFilesystemCache($app, $options);
                    break;
                case 'chain':
                    return $this->createChainCache($app, $options);
                    break;
                case 'memcache':
                    return $this->createMemcacheCache($app, $options);
                    break;
                case 'memcached':
                    return $this->createMemcachedCache($app, $options);
                    break;
                case 'couchbase':
                    return $this->createCouchbaseCache($app, $options);
                    break;
                case 'phpfile':
                    return $this->createPhpFileCache($app, $options);
                    break;
                case 'predis':
                    return $this->createPredisCache($app, $options);
                    break;
                case 'riak':
                    return $this->createRiakCache($app, $options);
                    break;
                case 'sqlite3':
                    return $this->createSqlite3Cache($app, $options);
                    break;
                case 'void':
                    return $this->createVoidCache($app);
                    break;
                case 'wincache':
                    return $this->createWinCacheCache($app);
                    break;
                case 'zenddata':
                    return $this->createZendDataCache($app);
                    break;
                case 'pdo':
                    return $this->createPDOCache($app, $options);
                    break;
            }

            throw new \RuntimeException("Cache Driver <{$driver}> not supported");
        });

        // shortcuts for the "first" cache
        $app['cache'] = function (Container $app) {
            /** @var Container $profiles */
            $profiles = $app['cache.stores'];

            return $profiles[array_shift($profiles->keys())];
        };

        $app['cache.default_options'] = [
            'driver' => 'array',
            'namespace' => null,
            'parameters' => []
        ];
    }

    /**
     * @param Container $app
     * @return ArrayCache
     */
    private function createArrayCache(Container $app)
    {
        $app['cache.store.array'] = $app->protect(function () {
            return new ArrayCache();
        });

        return $app['cache.store.array']();
    }

    /**
     * @param Container $app
     * @return ApcuCache
     */
    private function createApcuCache(Container $app)
    {
        $app['cache.store.apcu'] = $app->protect(function () {
            return new ApcuCache();
        });

        return $app['cache.store.apcu']();
    }

    /**
     * @param Container $app
     * @param array $options
     * @return RedisCache
     */
    private function createRedisCache(Container $app, array $options)
    {
        $app['cache.redis.connector'] = $app->protect(function ($options) {
            if (empty($options['host']) || empty($options['port'])) {
                throw new \InvalidArgumentException('You must specify "host" and "port" for Redis.');
            }

            $redis = new \Redis();
            $redis->connect($options['host'], $options['port']);

            if (isset($options['password'])) {
                $redis->auth($options['password']);
            }

            return $redis;
        });

        $app['cache.store.redis'] = $app->protect(function ($options) {
            $redis = $app['cache.redis.connector']($options);

            $cache = new RedisCache();
            $cache->setRedis($redis);

            return $cache;
        });

        return $app['cache.store.redis']($options);
    }

    /**
     * @param Container $app
     * @return XcacheCache
     */
    private function createXcacheCache(Container $app)
    {
        $app['cache.store.xcache'] = $app->protect(function () {
            return new XcacheCache();
        });

        return $app['cache.store.xcache']();
    }

    /**
     * @param Container $app
     * @param array $options
     * @return MongoDBCache
     */
    private function createMongoDBCache(Container $app, array $options)
    {
        $app['cache.mongodb.connector'] = $app->protect(function ($options) {
            if (empty($options['server'])
                || empty($options['name'])
                || empty($options['collection'])
            ) {
                throw new \InvalidArgumentException(
                    'You must specify "server", "name" and "collection" for MongoDB.'
                );
            }

            $client = new \MongoClient($options['server']);
            $db = new \MongoDB($client, $options['name']);
            $collection = new \MongoCollection($db, $options['collection']);

            return $collection;
        });

        $app['cache.store.mongodb'] = $app->protect(function ($options) {
            $collection = $app['cache.mongodb.connector']($options);

            return new MongoDBCache($collection);
        });

        return $app['cache.store.mongodb']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return FilesystemCache
     */
    private function createFilesystemCache(Container $app, array $options)
    {
        $app['cache.store.filesystem'] = $app->protect(function ($options) {
            if (empty($options['cache_dir']) || false === is_dir($options['cache_dir'])) {
                throw new \InvalidArgumentException(
                    'You must specify "cache_dir" for Filesystem.'
                );
            }

            return new FilesystemCache($options['cache_dir']);
        });

        return $app['cache.store.filesystem']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return ChainCache
     */
    private function createChainCache(Container $app, array $options)
    {
        $app['cache.store.chain'] = $app->protect(function ($options) use ($app) {
            if (false === is_array($options)) {
                throw new \InvalidArgumentException('You must specify array for Chain Cache.');
            }

            $caches = [];

            foreach ($options as $option) {
                $caches[] = $app['cache.factory']($option['driver'], $option['parameters']);
            }

            return new ChainCache($caches);
        });

        return $app['cache.store.chain']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return MemcacheCache
     */
    private function createMemcacheCache(Container $app, array $options)
    {
        $app['cache.memcache.connector'] = $app->protect(function ($options) {
            if (true === empty($options['host']) or true === empty($options['port'])) {
                throw new \InvalidArgumentException('You must specify "host" and "port" for Memcache.');
            }

            $memcache = new \Memcache();
            $memcache->connect($options['host'], $options['port']);

            return $memcache;
        });

        $app['cache.store.memcache'] = $app->protect(function ($options) {
            $memcache = $app['cache.memcache.connector']($options);

            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);

            return $cache;
        });

        return $app['cache.store.memcache']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return MemcachedCache
     */
    private function createMemcachedCache(Container $app, array $options)
    {
        $app['cache.memcached.connector'] = $app->protect(function ($options) {
            if (true === empty($options['host']) or true === empty($options['port'])) {
                throw new \InvalidArgumentException('You must specify "host" and "port" for Memcached.');
            }

            $memcached = new \Memcached();
            $memcached->addServer($options['host'], $options['port']);

            return $memcached;
        });

        $app['cache.store.memcached'] = $app->protect(function ($options) {
            $memcached = $app['cache.memcached.connector']($options);

            $cache = new MemcachedCache();
            $cache->setMemcached($memcached);

            return $cache;
        });

        return $app['cache.store.memcached']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return CouchbaseCache
     */
    private function createCouchbaseCache(Container $app, array $options)
    {
        $app['cache.couchbase.connector'] = $app->protect(function ($options) {
            if (true === empty($options['host']) or true === empty($options['port'])) {
                throw new \InvalidArgumentException('You must specify "host" and "port" for Couchbase.');
            }

            if (false === isset($options['username'])) {
                $options['username'] = '';
            }

            if (false === isset($options['password'])) {
                $options['password'] = '';
            }

            if (false === isset($options['bucket'])) {
                $options['bucket'] = 'default';
            }

            $couchbase = new \Couchbase(sprintf('%s:%s', $options['host'], $options['port']), $options['username'], $options['password'], $options['bucket']);

            return $couchbase;
        });

        $app['cache.store.couchbase'] = $app->protect(function ($options) {
            $couchbase = $app['cache.couchbase.connector']($options);

            $cache = new CouchbaseCache();
            $cache->setCouchbase($couchbase);

            return $cache;
        });

        return $app['cache.store.couchbase']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return PhpFileCache
     */
    private function createPhpFileCache(Container $app, array $options)
    {
        $app['cache.store.phpfile'] = $app->protect(function ($options) {
            if (true === empty($options['directory'])) {
                throw new \InvalidArgumentException('You must specify "directory" for PhpFile Cache.');
            }

            if (false === isset($options['extension'])) {
                $options['extension'] = '.doctrinecache.php';
            }

            if (false === isset($options['umask'])) {
                $options['umask'] = 0002;
            }

            return new PhpFileCache($options['directory'], $options['extension'], $options['umask']);
        });

        return $app['cache.store.phpfile']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return PredisCache
     */
    private function createPredisCache(Container $app, array $options)
    {
        $app['cache.predis.connector'] = $app->protect(function ($options) {
            if (true === empty($options['scheme'])) {
                throw new \InvalidArgumentException('You must specify "scheme" for Predis.');
            }

            if ((true === empty($options['host']) or true === empty($options['port'])) or (true === empty($options['path']))) {
                throw new \InvalidArgumentException('You must specify "host" and "port" or "path" for Predis.');
            }

            if (false === isset($options['options'])) {
                $options['options'] = [];
            }

            /** @var \Predis\ClientInterface $predis */
            $predis = new \Predis\Client([
                'scheme' => $options['scheme'],
                'host' => (true === isset($options['host']) and false === isset($options['path'])) ? $options['host'] : '127.0.0.1',
                'port' => (true === isset($options['port']) and false === isset($options['path'])) ? $options['port'] : 6379,
                'path' => (true === isset($options['path']) and false === isset($options['host']) and false === isset($options['port'])) ? $options['path'] : ''
            ], $options['options']);

            return $predis;
        });

        $app['cache.store.predis'] = $app->protect(function ($options) {
            $predis = $app['cache.predis.connector']($options);

            return new PredisCache($predis);
        });

        return $app['cache.store.predis']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return RiakCache
     */
    private function createRiakCache(Container $app, array $options)
    {
        $app['cache.riak.connector'] = $app->protect(function ($options) {
            if (true === empty($options['host']) or true === empty($options['port']) or true === empty($options['bucket'])) {
                throw new \InvalidArgumentException('You must specify "host", "port" and "bucket" for Riak.');
            }

            $connection = new \Riak\Connection($options['host'], $options['port']);

            $bucket = new \Riak\Bucket($connection, $options['bucket']);

            return $bucket;
        });

        $app['cache.store.riak'] = $app->protect(function ($options) {
            $bucket = $app['cache.riak.connector']($options);

            return new RiakCache($bucket);
        });

        return $app['cache.store.riak']($options);
    }

    /**
     * @param Container $app
     * @param array $options
     * @return Sqlite3Cache
     */
    private function createSqlite3Cache(Container $app, array $options)
    {
        $app['cache.sqlite3.connector'] = $app->protect(function ($options) {
            if (true === empty($options['filename']) or true === empty($options['table'])) {
                throw new \InvalidArgumentException('You must specify "filename" and "table" for Sqlite3.');
            }

            if (false === isset($options['flags'])) {
                $options['flags'] = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
            }

            if (false === isset($options['encryption_key'])) {
                $options['encryption_key'] = null;
            }

            $sqlite3 = new \SQLite3($options['file'], $options['flags'], $options['encryption_key']);

            return $sqlite3;
        });

        $app['cache.store.sqlite3'] = $app->protect(function ($options) {
            $sqlite3 = $app['cache.sqlite3.connector']($options);

            return new SQLite3Cache($sqlite3, $options['table']);
        });

        return $app['cache.store.sqlite3']($options);
    }

    /**
     * @param Container $app
     * @return VoidCache
     */
    private function createVoidCache(Container $app)
    {
        $app['cache.store.void'] = $app->protect(function () {
            return new VoidCache();
        });

        return $app['cache.store.void']();
    }

    /**
     * @param Container $app
     * @return WinCacheCache
     */
    private function createWinCacheCache(Container $app)
    {
        $app['cache.wincache'] = $app->protect(function () {
            return new WinCacheCache();
        });

        return $app['cache.store.wincache']();
    }

    /**
     * @param Container $app
     * @return ZendDataCache
     */
    private function createZendDataCache(Container $app)
    {
        $app['cache.store.zenddata'] = $app->protect(function () {
            return new ZendDataCache();
        });

        return $app['cache.store.zenddata']();
    }

    /**
     * @param Container $app
     * @param array $options
     * @return PDOCache
     */
    private function createPDOCache(Container $app, array $options)
    {
        $app['cache.pdo.connector'] = $app->protect(function ($options) {
            if (true === empty($options['dns']) or true === empty($options['table'])) {
                throw new \InvalidArgumentException('You must specify "dns" and "table" for PDO.');
            }

            if (false === isset($options['username'])) {
                $options['username'] = '';
            }

            if (false === isset($options['password'])) {
                $options['password'] = '';
            }

            if (false === isset($options['options'])) {
                $options['options'] = [];
            }

            $pdo = new \PDO($options['dns'], $options['username'], $options['password'], $options['options']);

            return $pdo;
        });

        $app['cache.store.pdo'] = $app->protect(function ($options) {
            $pdo = $app['cache.pdo.connector']($options);

            return new PDOCache($pdo, $options['table']);
        });

        return $app['cache.store.pdo']($options);
    }
}
