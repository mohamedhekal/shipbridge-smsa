<?php

declare(strict_types=1);

namespace Hekal\ShipBridge\Smsa;

use Hekal\ShipBridge\Exceptions\ShipBridgeException;
use Hekal\ShipBridge\Smsa\Contracts\SmsaGateway;
use SoapClient;
use SoapFault;

/**
 * SMSA Express SECOM SOAP API.
 *
 * WSDL: http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?WSDL
 */
final class SoapSmsaGateway implements SmsaGateway
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function addShip(array $params): string
    {
        $result = $this->call('addShip', $params);
        $awb = $this->scalarResult($result, 'addShipResult');

        if ($this->looksLikeError($awb)) {
            throw ShipBridgeException::carrierFailed('SMSA addShip failed: '.$awb);
        }

        return $awb;
    }

    public function getStatus(string $awb): array
    {
        $result = $this->call('getStatus', [
            'awbNo' => $awb,
            'passkey' => $this->passKey(),
        ]);

        return [
            'status' => $this->scalarResult($result, 'getStatusResult'),
            'raw' => json_decode(json_encode($result) ?: '{}', true) ?: [],
        ];
    }

    public function getTrack(string $awb): array
    {
        $result = $this->call('getTrack', [
            'awbNo' => $awb,
            'passkey' => $this->passKey(),
        ]);

        $payload = json_decode(json_encode($result) ?: '{}', true) ?: [];

        return is_array($payload) ? $payload : [];
    }

    public function getPdf(string $awb): string
    {
        $result = $this->call('getPDF', [
            'awbNo' => $awb,
            'passKey' => $this->passKey(),
        ]);

        return $this->scalarResult($result, 'getPDFResult');
    }

    public function cancelShipment(string $awb, string $reason): string
    {
        $result = $this->call('cancelShipment', [
            'awbNo' => $awb,
            'passkey' => $this->passKey(),
            'reas' => $reason,
        ]);

        return $this->scalarResult($result, 'cancelShipmentResult');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function call(string $method, array $params): mixed
    {
        $wsdl = (string) ($this->config['wsdl'] ?? 'http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?WSDL');

        try {
            $client = new SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => (int) ($this->config['timeout'] ?? 45),
                'cache_wsdl' => WSDL_CACHE_BOTH,
            ]);

            return $client->__soapCall($method, [$params]);
        } catch (SoapFault $e) {
            throw ShipBridgeException::carrierFailed('SMSA SOAP: '.$e->getMessage());
        }
    }

    private function passKey(): string
    {
        $key = $this->config['passkey'] ?? $this->config['pass_key'] ?? $this->config['token'] ?? null;
        if (! is_string($key) || $key === '') {
            throw ShipBridgeException::carrierFailed('SMSA requires SMSA_PASSKEY.');
        }

        return $key;
    }

    private function scalarResult(mixed $result, string $property): string
    {
        if (is_object($result) && isset($result->{$property})) {
            return trim((string) $result->{$property});
        }

        if (is_array($result) && isset($result[$property])) {
            return trim((string) $result[$property]);
        }

        if (is_scalar($result)) {
            return trim((string) $result);
        }

        throw ShipBridgeException::carrierFailed("SMSA response missing {$property}.");
    }

    private function looksLikeError(string $value): bool
    {
        $lower = strtolower($value);

        return $value === ''
            || str_contains($lower, 'error')
            || str_contains($lower, 'failed')
            || str_contains($lower, 'invalid');
    }
}
