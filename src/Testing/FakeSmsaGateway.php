<?php

declare(strict_types=1);

namespace Hekal\ShipBridge\Smsa\Testing;

use Hekal\ShipBridge\Smsa\Contracts\SmsaGateway;

/**
 * In-memory SMSA gateway for Pest tests (no live SOAP).
 */
final class FakeSmsaGateway implements SmsaGateway
{
    /** @var list<array{method: string, params: array<string, mixed>}> */
    public array $calls = [];

    public function addShip(array $params): string
    {
        $this->calls[] = ['method' => 'addShip', 'params' => $params];

        return '290019315792';
    }

    public function getStatus(string $awb): array
    {
        $this->calls[] = ['method' => 'getStatus', 'params' => ['awb' => $awb]];

        return ['status' => 'OUT FOR DELIVERY', 'raw' => []];
    }

    public function getTrack(string $awb): array
    {
        $this->calls[] = ['method' => 'getTrack', 'params' => ['awb' => $awb]];

        return [
            'getTrackResult' => [
                'TrackDetailsList' => [
                    'TrackDetails' => [
                        [
                            'Activity' => 'OUT FOR DELIVERY',
                            'Details' => 'With courier',
                            'Date' => '2026-07-16',
                            'Time' => '12:00',
                            'Location' => 'Jeddah',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getPdf(string $awb): string
    {
        $this->calls[] = ['method' => 'getPDF', 'params' => ['awb' => $awb]];

        return base64_encode('%PDF-1.4 fake-smsa');
    }

    public function cancelShipment(string $awb, string $reason): string
    {
        $this->calls[] = ['method' => 'cancelShipment', 'params' => ['awb' => $awb, 'reason' => $reason]];

        return 'Success';
    }
}
