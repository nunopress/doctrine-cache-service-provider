Doctrine Cache Service Provider
-------------------------------

>
> The documentation still in progress, this is the first fork for our version. In the future release's we change the documentation following our standard's.
>
> We added all the Doctrine Cache Driver but some not tested, so please send a issue for every problem you can have using our Service Provider.
>


[![Build Status](https://travis-ci.org/sergiors/doctrine-cache-service-provider.svg?branch=1.0.0)](https://travis-ci.org/sergiors/doctrine-cache-service-provider)

To see the complete documentation, check out [Doctrine Cache](http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/caching.html)

Install
-------
```
composer require nunopress/doctrine-cache-service-provider
```

How to use
----------
```php
$app->register(new \NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'cache.options' => [
        'driver' => 'redis',
        'namespace' => 'myapp',
        'host' => '{your_host}',
        'port' => '{your_portt}',
        // 'password' => ''
    ]
]);

// $app['cache']->save('cache_id', 'my_data');
// $app['cache']->fetch('cache_id');
```

## Multiple instances

Something like this:
```php
$app->register(new \NunoPress\Silex\Provider\DoctrineCacheServiceProvider(), [
    'caches.options' = [
        'conn1' => 'xcache',
        'conn2' => [
            'driver' => 'redis'
        ],
        'conn3' => [
            'driver' => 'array',
            'namespace' => 'test'
        ]
    ]
]);

// $app['caches']['conn1'];
// $app['caches']['conn2'];
// $app['caches']['conn3'];
```

Be Happy!

License
-------
MIT
