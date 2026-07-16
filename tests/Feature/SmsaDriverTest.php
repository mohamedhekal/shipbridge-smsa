<?php

declare(strict_types=1);

use Hekal\ShipBridge\DTOs\Address;
use Hekal\ShipBridge\DTOs\CreateShipmentRequest;
use Hekal\ShipBridge\DTOs\Parcel;
use Hekal\ShipBridge\DTOs\ReturnShipmentRequest;
use Hekal\ShipBridge\Enums\ShipmentStatus;
use Hekal\ShipBridge\Exceptions\ShipBridgeException;
use Hekal\ShipBridge\Facades\ShipBridge;

it('creates a SMSA shipment via addShip', function (): void {
    $result = ShipBridge::driver('smsa')->createShipment(new CreateShipmentRequest(
        origin: new Address('Warehouse LLC', '1 King Rd', 'Riyadh', 'SA', phone: '0110000000'),
        destination: new Address('Customer', '10 Fahad Rd', 'Jeddah', 'SA', phone: '0555555555'),
        parcels: [new Parcel(weightKg: 2.0, description: 'Clothes')],
        reference: 'ORD-42',
        metadata: ['cod' => 50],
    ));

    expect($result->trackingNumber)->toBe('290019315792')
        ->and($result->carrier)->toBe('smsa')
        ->and($result->status)->toBe(ShipmentStatus::Created);

    $call = $this->gateway->calls[0] ?? null;
    expect($call['method'] ?? null)->toBe('addShip')
        ->and($call['params']['cMobile'] ?? null)->toBe('0555555555')
        ->and($call['params']['cCity'] ?? null)->toBe('JEDDAH')
        ->and($call['params']['codAmt'] ?? null)->toBe('50')
        ->and($call['params']['shipType'] ?? null)->toBe('DLV');
});

it('tracks a SMSA awb', function (): void {
    $tracking = ShipBridge::driver('smsa')->track('290019315792');

    expect($tracking->status)->toBe(ShipmentStatus::OutForDelivery)
        ->and($tracking->events)->not->toBeEmpty();
});

it('fetches AWB PDF', function (): void {
    $label = ShipBridge::driver('smsa')->label('290019315792');

    expect($label->contents)->not->toBeEmpty()
        ->and($label->base64Encoded)->toBeTrue();
});

it('creates a return with RET ship type', function (): void {
    $result = ShipBridge::driver('smsa')->createReturn(new ReturnShipmentRequest(
        originalShipmentId: '290019315792',
        returnTo: new Address('Warehouse', '1 King Rd', 'Riyadh', 'SA', phone: '0110000000'),
        pickupFrom: new Address('Customer', '10 Fahad Rd', 'Jeddah', 'SA', phone: '0555555555'),
        reason: 'Wrong size',
    ));

    expect($result->status)->toBe(ShipmentStatus::Returned);

    $call = collect($this->gateway->calls)->last();
    expect($call['params']['shipType'] ?? null)->toBe('RET');
});

it('requires consignee phone', function (): void {
    ShipBridge::driver('smsa')->createShipment(new CreateShipmentRequest(
        origin: new Address('Warehouse', '1 King Rd', 'Riyadh', 'SA'),
        destination: new Address('Customer', '10 Fahad Rd', 'Jeddah', 'SA'),
        parcels: [new Parcel(weightKg: 1.0)],
    ));
})->throws(ShipBridgeException::class);
