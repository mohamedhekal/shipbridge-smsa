<?php

declare(strict_types=1);

use Hekal\ShipBridge\DTOs\Address;
use Hekal\ShipBridge\DTOs\CreateShipmentRequest;
use Hekal\ShipBridge\DTOs\Parcel;
use Hekal\ShipBridge\Enums\ShipmentStatus;
use Hekal\ShipBridge\Facades\ShipBridge;
use Illuminate\Support\Facades\Http;

it('creates a SMSA Express shipment through ShipBridge', function (): void {
    Http::fake([
        'https://smsa.test/v1/shipments' => Http::response([
            'id' => 'SMSA-1',
            'tracking_number' => 'TRK-SMSA-1',
            'status' => 'created',
            'carrier' => 'smsa',
            'label_url' => 'https://labels.test/smsa.pdf',
        ], 200),
    ]);

    $result = ShipBridge::driver('smsa')->createShipment(new CreateShipmentRequest(
        origin: new Address('Warehouse', '1 Industrial Rd', 'Cairo', 'EG'),
        destination: new Address('Customer', '12 Nile St', 'Giza', 'EG', phone: '01000000000'),
        parcels: [new Parcel(weightKg: 1.5)],
        reference: 'ORD-100',
    ));

    expect($result->id)->toBe('SMSA-1')
        ->and($result->trackingNumber)->toBe('TRK-SMSA-1')
        ->and($result->carrier)->toBe('smsa')
        ->and($result->status)->toBe(ShipmentStatus::Created);
});

it('tracks a SMSA Express shipment', function (): void {
    Http::fake([
        'https://smsa.test/v1/shipments/track/*' => Http::response([
            'tracking_number' => 'TRK-1',
            'status' => 'in_transit',
            'events' => [
                [
                    'status' => 'picked_up',
                    'description' => 'Picked up',
                    'occurred_at' => '2026-07-16T10:00:00Z',
                    'location' => 'Cairo',
                ],
            ],
        ], 200),
    ]);

    $tracking = ShipBridge::driver('smsa')->track('TRK-1');

    expect($tracking->status)->toBe(ShipmentStatus::InTransit)
        ->and($tracking->events)->toHaveCount(1);
});
