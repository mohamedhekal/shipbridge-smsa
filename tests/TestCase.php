<?php

declare(strict_types=1);

namespace Hekal\ShipBridge\Smsa\Tests;

use Hekal\ShipBridge\ShipBridgeServiceProvider;
use Hekal\ShipBridge\Smsa\SmsaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ShipBridgeServiceProvider::class,
            SmsaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('shipbridge.default', 'smsa');
        $app['config']->set('shipbridge.drivers.smsa.base_url', 'https://smsa.test/v1');
        $app['config']->set('shipbridge.drivers.smsa.token', 'test-token');
    }
}
