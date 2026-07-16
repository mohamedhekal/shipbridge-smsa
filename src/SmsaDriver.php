<?php

declare(strict_types=1);

namespace Hekal\ShipBridge\Smsa;

use Hekal\ShipBridge\Contracts\CarrierDriver;
use Hekal\ShipBridge\DTOs\CreateShipmentRequest;
use Hekal\ShipBridge\DTOs\ExchangeShipmentRequest;
use Hekal\ShipBridge\DTOs\LabelResult;
use Hekal\ShipBridge\DTOs\ReturnShipmentRequest;
use Hekal\ShipBridge\DTOs\ShipmentResult;
use Hekal\ShipBridge\DTOs\TrackingEvent;
use Hekal\ShipBridge\DTOs\TrackingResult;
use Hekal\ShipBridge\Enums\LabelFormat;
use Hekal\ShipBridge\Enums\ShipmentStatus;
use Hekal\ShipBridge\Exceptions\ShipBridgeException;
use Hekal\ShipBridge\Smsa\Contracts\SmsaGateway;
use Hekal\ShipBridge\Smsa\Support\PayloadFactory;
use Hekal\ShipBridge\Support\StatusNormalizer;

/**
 * SMSA Express (Saudi Arabia) SECOM SOAP driver.
 */
final class SmsaDriver implements CarrierDriver
{
    public function __construct(
        private readonly SmsaGateway $gateway,
        private readonly PayloadFactory $payloads,
        private readonly StatusNormalizer $normalizer,
    ) {}

    public function createShipment(CreateShipmentRequest $request): ShipmentResult
    {
        $awb = $this->gateway->addShip($this->payloads->create($request));

        return new ShipmentResult(
            id: $awb,
            trackingNumber: $awb,
            status: ShipmentStatus::Created,
            carrier: 'smsa',
            labelUrl: null,
            raw: ['awb' => $awb],
        );
    }

    public function track(string $trackingNumber): TrackingResult
    {
        $track = $this->gateway->getTrack($trackingNumber);
        $events = $this->parseTrackEvents($track);

        if ($events === []) {
            $statusPayload = $this->gateway->getStatus($trackingNumber);
            $statusRaw = (string) ($statusPayload['status'] ?? 'exception');
            $status = $this->normalizer->normalize($statusRaw);
            $events[] = new TrackingEvent(status: $status, description: $statusRaw);

            return new TrackingResult(
                trackingNumber: $trackingNumber,
                status: $status,
                events: $events,
                raw: $statusPayload,
            );
        }

        return new TrackingResult(
            trackingNumber: $trackingNumber,
            status: $events[0]->status,
            events: $events,
            raw: $track,
        );
    }

    public function label(string $shipmentId, LabelFormat $format = LabelFormat::Pdf): LabelResult
    {
        $contents = $this->gateway->getPdf($shipmentId);
        if ($contents === '') {
            throw ShipBridgeException::carrierFailed('SMSA getPDF returned empty label.');
        }

        $base64 = ! str_starts_with($contents, '%PDF');

        return new LabelResult(
            shipmentId: $shipmentId,
            format: $format,
            contents: $contents,
            base64Encoded: $base64,
            url: null,
        );
    }

    public function createReturn(ReturnShipmentRequest $request): ShipmentResult
    {
        $awb = $this->gateway->addShip($this->payloads->returnShipment($request));

        return new ShipmentResult(
            id: $awb,
            trackingNumber: $awb,
            status: ShipmentStatus::Returned,
            carrier: 'smsa',
            raw: ['awb' => $awb],
        );
    }

    public function createExchange(ExchangeShipmentRequest $request): ShipmentResult
    {
        $awb = $this->gateway->addShip($this->payloads->exchange($request));

        return new ShipmentResult(
            id: $awb,
            trackingNumber: $awb,
            status: ShipmentStatus::Exchanged,
            carrier: 'smsa',
            raw: ['awb' => $awb],
        );
    }

    /**
     * @param  array<string, mixed>  $track
     * @return list<TrackingEvent>
     */
    private function parseTrackEvents(array $track): array
    {
        $details = data_get($track, 'getTrackResult.TrackDetailsList.TrackDetails')
            ?? data_get($track, 'TrackDetailsList.TrackDetails')
            ?? data_get($track, 'getTrackResult');

        if (! is_array($details) || $details === []) {
            return [];
        }

        // Single TrackDetails object vs list
        if (isset($details['Activity']) || isset($details['Details'])) {
            $details = [$details];
        }

        /** @var list<TrackingEvent> $events */
        $events = [];
        foreach ($details as $row) {
            if (! is_array($row)) {
                continue;
            }
            $activity = (string) ($row['Activity'] ?? $row['activity'] ?? $row['Status'] ?? '');
            $desc = (string) ($row['Details'] ?? $row['details'] ?? $activity);
            if ($activity === '' && $desc === '') {
                continue;
            }
            $date = (string) ($row['Date'] ?? $row['date'] ?? '');
            $time = (string) ($row['Time'] ?? $row['time'] ?? '');
            $occurred = trim($date.' '.$time);

            $events[] = new TrackingEvent(
                status: $this->normalizer->normalize($activity !== '' ? $activity : $desc),
                description: $desc !== '' ? $desc : $activity,
                occurredAt: $occurred !== '' ? $occurred : null,
                location: isset($row['Location']) ? (string) $row['Location'] : (isset($row['location']) ? (string) $row['location'] : null),
            );
        }

        return $events;
    }
}
