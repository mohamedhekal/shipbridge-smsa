<?php

declare(strict_types=1);

namespace Hekal\ShipBridge\Smsa\Contracts;

interface SmsaGateway
{
    /**
     * @param  array<string, mixed>  $params
     */
    public function addShip(array $params): string;

    /**
     * @return array<string, mixed>
     */
    public function getStatus(string $awb): array;

    /**
     * @return array<string, mixed>
     */
    public function getTrack(string $awb): array;

    public function getPdf(string $awb): string;

    public function cancelShipment(string $awb, string $reason): string;
}
