<?php

declare(strict_types=1);

namespace Hekal\ShipBridge\Smsa\Tests;

use Hekal\ShipBridge\ShipBridgeServiceProvider;
use Hekal\ShipBridge\Smsa\Contracts\SmsaGateway;
use Hekal\ShipBridge\Smsa\SmsaServiceProvider;
use Hekal\ShipBridge\Smsa\Testing\FakeSmsaGateway;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected FakeSmsaGateway $gateway;

    protected function getPackageProviders($app): array
    {
        return [
            ShipBridgeServiceProvider::class,
            SmsaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $this->gateway = new FakeSmsaGateway;

        $app->instance(SmsaGateway::class, $this->gateway);

        $app['config']->set('shipbridge.default', 'smsa');
        $app['config']->set('shipbridge.drivers.smsa.passkey', 'Testing0');
        $app['config']->set('shipbridge.drivers.smsa.fake', true);
    }
}
