<?php

namespace NunoPress\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MongoDBCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\CouchbaseCache;
use Doctrine\Common\Cache\FileCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\PredisCache;
use Doctrine\Common\Cache\RiakCache;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class DoctrineCacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['caches.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['caches.options'])) {
                $app['caches.options'] = [
                    'default' => isset($app['cache.options'])
                        ? $app['cache.options']
                        : []
                ];
            }

            $app['caches.options'] = array_map(function ($options) use ($app) {
                return array_replace($app['cache.default_options'], is_array($options)
                    ? $options
                    : ['driver' => $options]
                );
            }, $app['caches.options']);

            if (!isset($app['caches.default'])) {
                $app['caches.default'] = array_keys(array_slice($app['caches.options'], 0, 1))[0];
            }
        });

        $app['caches'] = function (Container $app) {
            $app['caches.options.initializer']();

            $container = new Container();
            foreach ($app['caches.options'] as $name => $options) {
                $container[$name] = function () use ($app, $options) {
                    $cache = $app['cache.factory']($options['driver'], $options);
                    $cache->setNamespace($options['namespace']);

                    return $cache;
                };
            }

            return $container;
        };

        $app['cache.filesystem'] = $app->protect(function ($options) {
            if (empty($options['cache_dir']) || false === is_dir($options['cache_dir'])) {
                throw new \InvalidArgumentException(
                    'You must specify "cache_dir" for Filesystem.'
                );
            }

            return new FilesystemCache($options['cache_dir']);
        });

        $app['cache.array'] = $app->protect(function () {
            return new ArrayCache();
        });

        $app['cache.apcu'] = $app->protect(function () {
            return new ApcuCache();
        });

        $app['cache.mongodb'] = $app->protect(function ($options) {
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

            return new MongoDBCache($collection);
        });

        $app['cache.redis'] = $app->protect(function ($options) {
            if (empty($options['host']) || empty($options['port'])) {
                throw new \InvalidArgumentException('You must specify "host" and "port" for Redis.');
            }

            $redis = new \Redis();
            $redis->connect($options['host'], $options['port']);

            if (isset($options['password'])) {
                $redis->auth($options['password']);
            }

            $cache = new RedisCache();
            $cache->setRedis($redis);

            return $cache;
        });

        $app['cache.xcache'] = $app->protect(function () {
            return new XcacheCache();
        });

        $app['cache.chain'] = $app->protect(function ($options) {
            if (true === empty($options['providers']) or false === is_array($options['providers'])) {
                throw new \InvalidArgumentException('You must specify "providers" array for Chain Cache.');
            }

            return new ChainCache($options['providers']);
        });

        $app['cache.memcache'] = $app->protect(function ($options) {
            if (true === empty($options['host']) or true === empty($options['port'])) {
                throw new \InvalidArgumentException('You must specify "host" and "port" for Memcache.');
            }

            $memcache = new Memcache();
            $memcache->connect($options['host'], $options['port']);

            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);

            return $cache;
        });

        $app['cache.memcached'] = $app->protect(function ($options) {
            if (true === empty($options['host']) or true === empty($options['port'])) {
                throw new \InvalidArgumentException('You must specify "host" and "port" for Memcached.');
            }

            $memcached = new Memcached();
            $memcached->addServer($options['host'], $options['port']);

            $cache = new MemcachedCache();
            $cache->setMemcached($memcached);

            return $cache;
        });

        $app['cache.apc'] = $app->protect(function () {
            return new ApcCache();
        });

        $app['cache.couchbase'] = $app->protect(function ($options) {
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

            $cache = new CouchbaseCache();
            $cache->setCouchbase($couchbase);

            return $cache;
        });

        $app['cache.file'] = $app->protect(function ($options) {
            if (true === empty($options['directory'])) {
                throw new \InvalidArgumentException('You must specify "directory" for File Cache.');
            }

            if (false === isset($options['extension'])) {
                $options['extension'] = '';
            }

            if (false === isset($options['umask'])) {
                $options['umask'] = 0002;
            }

            return new FileCache($options['directory'], $options['extension'], $options['umask']);
        });

        $app['cache.phpfile'] = $app->protect(function ($options) {
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

        $app['cache.predis'] = $app->protect(function ($options) {
            if (true === empty($options['scheme'])) {
                throw new \InvalidArgumentException('You must specify "scheme" for Predis.');
            }

            if ((true === empty($options['host']) or true === empty($options['port'])) or (true === empty($options['path']))) {
                throw new \InvalidArgumentException('You must specify "host" and "port" or "path" for Predis.');
            }

            if (false === isset($options['options'])) {
                $options['options'] = [];
            }

            $predis = new \Predis\Client([
                'scheme' => $options['scheme'],
                'host' => (true === isset($options['host'] and false === isset($options['path']))) ? $options['host'] : '127.0.0.1',
                'port' => (true === isset($options['port'] and false === isset($options['path']))) ? $options['port'] : 6379,
                'path' => (true === isset($options['path']) and false === isset($options['host'] and false === isset($options['port']))) ? $options['path'] : ''
            ], $options['options']);

            return new PredisCache($predis);
        });

        $app['cache.riak'] = $app->protect(function ($options) {
            if (true === empty($options['host']) or true === empty($options['port']) or true === empty($options['bucket'])) {
                throw new \InvalidArgumentException('You must specify "host", "port" and "bucket" for Riak.');
            }

            $connection = new \Riak\Connection($options['host'], $options['port']);

            $bucket = new \Riak\Bucket($connection, $options['bucket']);

            return new RiakCache($bucket);
        });

        $app['cache.factory'] = $app->protect(function ($driver, $options) use ($app) {
            switch ($driver) {
                case 'array':
                    return $app['cache.array']();
                    break;
                case 'apcu':
                    return $app['cache.apcu']();
                    break;
                case 'redis':
                    return $app['cache.redis']($options);
                    break;
                case 'xcache':
                    return $app['cache.xcache']();
                    break;
                case 'mongodb':
                    return $app['cache.mongodb']($options);
                    break;
                case 'filesystem':
                    return $app['cache.filesystem']($options);
                    break;
                case 'chain':
                    return $app['cache.chain']($options);
                    break;
                case 'memcache':
                    return $app['cache.memcache']($options);
                    break;
                case 'memcached':
                    return $app['cache.memcached']($options);
                    break;
                case 'apc':
                    return $app['cache.apc']();
                    break;
                case 'couchbase':
                    return $app['cache.couchbase']($options);
                    break;
                case 'file':
                    return $app['cache.file']($options);
                    break;
                case 'phpfile':
                    return $app['cache.phpfile']($options);
                    break;
                case 'predis':
                    return $app['cache.predis']($options);
                    break;
                case 'riak':
                    return $app['cache.riak']($options);
                    break;
            }

            throw new \RuntimeException();
        });

        // shortcuts for the "first" cache
        $app['cache'] = function (Container $app) {
            $caches = $app['caches'];

            return $caches[$app['caches.default']];
        };

        $app['cache.default_options'] = [
            'driver' => 'array',
            'namespace' => null,
        ];
    }
}
