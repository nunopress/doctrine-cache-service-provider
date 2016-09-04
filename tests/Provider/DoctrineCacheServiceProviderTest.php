<?php

namespace NunoPress\Silex\Tests\Provider;

use Pimple\Container;
use Doctrine\Common\Cache\ApcuCache;
use NunoPress\Silex\Provider\DoctrineCacheServiceProvider;

class DoctrineCacheServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function register()
    {
        $app = $this->createApplication();
        $app->register(new DoctrineCacheServiceProvider(), [
            'cache.profiles' => [
                'default' => [
                	'driver' => 'apcu'
                ],
            ],
        ]);

        $this->assertInstanceOf(ApcuCache::class, $app['cache']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldReturnInvalidArgumentException()
    {
        $app = $this->createApplication();
        $app->register(new DoctrineCacheServiceProvider(), [
            'cache.profiles' => [
                'default' => [
                	'driver' => 'filesystem'
                ]
            ],
        ]);
        $app['cache'];
    }

    /**
     * @test
     */
    public function multipleConnections()
    {
        $app = $this->createApplication();
        $app->register(new DoctrineCacheServiceProvider());
        $app['caches.profiles'] = [
            'conn1' => [
            	'driver' => 'xcache'
            ],
            'conn2' => [
                'driver' => 'redis',
            ],
            'conn3' => [
                'driver' => 'array',
                'namespace' => 'test',
            ],
        ];

        $this->assertSame($app['cache.stores']['conn1'], $app['cache']);
        $this->assertEquals('test', $app['cache.stores']['conn3']->getNamespace());
    }

    public function createApplication()
    {
        return new Container();
    }
}
