<?php

declare(strict_types=1);

namespace Hekal\ShipBridge\Smsa;

use Hekal\ShipBridge\Facades\ShipBridge;
use Hekal\ShipBridge\Support\StatusNormalizer;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

final class SmsaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/smsa.php', 'shipbridge.drivers.smsa');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/smsa.php' => config_path('shipbridge-smsa.php'),
        ], 'shipbridge-smsa-config');

        ShipBridge::extend('smsa', function ($app, array $config): SmsaDriver {
            /** @var array<string, string> $aliases */
            $aliases = config('shipbridge.status_aliases', []);
            /** @var array<string, string> $driverMap */
            $driverMap = $config['status_map'] ?? [];

            return new SmsaDriver(
                http: $app->make(HttpFactory::class),
                normalizer: new StatusNormalizer(array_merge($aliases, $driverMap)),
                config: $config,
            );
        });
    }
}
