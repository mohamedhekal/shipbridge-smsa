<?php

declare(strict_types=1);

namespace Hekal\ShipBridge\Smsa\Support;

use Hekal\ShipBridge\DTOs\Address;
use Hekal\ShipBridge\DTOs\CreateShipmentRequest;
use Hekal\ShipBridge\DTOs\ExchangeShipmentRequest;
use Hekal\ShipBridge\DTOs\Parcel;
use Hekal\ShipBridge\DTOs\ReturnShipmentRequest;
use Hekal\ShipBridge\Exceptions\ShipBridgeException;

/**
 * Maps ShipBridge DTOs → SMSA addShip SOAP parameters.
 */
final class PayloadFactory
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function create(CreateShipmentRequest $request): array
    {
        return $this->addShipParams(
            consignee: $request->destination,
            shipper: $request->origin,
            parcels: $request->parcels,
            reference: $request->reference,
            metadata: $request->metadata,
            shipType: (string) ($request->metadata['ship_type'] ?? $this->config['ship_type'] ?? 'DLV'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function returnShipment(ReturnShipmentRequest $request): array
    {
        $pickup = $request->pickupFrom ?? $request->returnTo;
        $meta = array_merge($request->metadata, [
            'item_desc' => $request->reason ?? 'Return shipment',
        ]);

        return $this->addShipParams(
            consignee: $request->returnTo,
            shipper: $pickup,
            parcels: $request->parcels ?? [new Parcel(weightKg: 1.0)],
            reference: $request->originalShipmentId,
            metadata: $meta,
            shipType: (string) ($meta['ship_type'] ?? 'RET'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function exchange(ExchangeShipmentRequest $request): array
    {
        $meta = array_merge($request->metadata, [
            'item_desc' => $request->reason ?? 'Exchange shipment',
        ]);

        return $this->addShipParams(
            consignee: $request->destination,
            shipper: $request->origin,
            parcels: $request->outboundParcels,
            reference: $request->originalShipmentId,
            metadata: $meta,
            shipType: (string) ($meta['ship_type'] ?? 'DLV'),
        );
    }

    /**
     * @param  list<Parcel>  $parcels
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function addShipParams(
        Address $consignee,
        Address $shipper,
        array $parcels,
        ?string $reference,
        array $metadata,
        string $shipType,
    ): array {
        $mobile = $consignee->phone ?? (isset($metadata['phone']) ? (string) $metadata['phone'] : null);
        if ($mobile === null || $mobile === '') {
            throw ShipBridgeException::carrierFailed('SMSA requires consignee mobile (Address::$phone).');
        }

        $city = (string) ($metadata['city'] ?? $consignee->city);
        if ($city === '') {
            throw ShipBridgeException::carrierFailed('SMSA requires consignee city.');
        }

        $weight = 0.0;
        foreach ($parcels as $parcel) {
            $weight += $parcel->weightKg;
        }
        if ($weight <= 0) {
            $weight = 1.0;
        }

        $cod = (float) ($metadata['cod'] ?? $metadata['cod_amt'] ?? 0);
        $desc = (string) ($metadata['item_desc'] ?? ($parcels[0]->description !== '' ? $parcels[0]->description : 'Goods'));

        $passKey = (string) ($this->config['passkey'] ?? $this->config['pass_key'] ?? $this->config['token'] ?? '');

        return [
            'passKey' => $passKey,
            'refNo' => (string) ($metadata['ref_no'] ?? $reference ?? ('SB'.time())),
            'sentDate' => (string) ($metadata['sent_date'] ?? date('Y-m-d')),
            'idNo' => (string) ($metadata['id_no'] ?? ''),
            'cName' => $consignee->name,
            'cntry' => strtoupper((string) ($metadata['country'] ?? $consignee->countryCode ?: 'SA')),
            'cCity' => strtoupper($city),
            'cZip' => (string) ($metadata['zip'] ?? $consignee->postalCode ?? ''),
            'cPOBox' => (string) ($metadata['po_box'] ?? ''),
            'cMobile' => $mobile,
            'cTel1' => (string) ($metadata['tel1'] ?? $mobile),
            'cTel2' => (string) ($metadata['tel2'] ?? ''),
            'cAddr1' => (string) ($metadata['addr1'] ?? $consignee->line1),
            'cAddr2' => (string) ($metadata['addr2'] ?? $consignee->line2 ?? ''),
            'shipType' => $shipType,
            'PCs' => (int) ($metadata['pcs'] ?? max(1, count($parcels))),
            'cEmail' => (string) ($metadata['email'] ?? $consignee->email ?? ''),
            'carrValue' => (string) ($metadata['carr_value'] ?? ''),
            'carrCurr' => (string) ($metadata['carr_curr'] ?? 'SAR'),
            'codAmt' => (string) $cod,
            'weight' => (string) $weight,
            'custVal' => (string) ($metadata['cust_val'] ?? ''),
            'custCurr' => (string) ($metadata['cust_curr'] ?? 'SAR'),
            'insrAmt' => (string) ($metadata['insr_amt'] ?? ''),
            'insrCurr' => (string) ($metadata['insr_curr'] ?? 'SAR'),
            'itemDesc' => $desc,
            'sName' => (string) ($metadata['shipper_name'] ?? $shipper->name),
            'sContact' => (string) ($metadata['shipper_contact'] ?? $shipper->name),
            'sAddr1' => (string) ($metadata['shipper_addr1'] ?? $shipper->line1),
            'sAddr2' => (string) ($metadata['shipper_addr2'] ?? $shipper->line2 ?? ''),
            'sCity' => strtoupper((string) ($metadata['shipper_city'] ?? $shipper->city)),
            'sPhone' => (string) ($metadata['shipper_phone'] ?? $shipper->phone ?? ''),
            'sCntry' => strtoupper((string) ($metadata['shipper_country'] ?? $shipper->countryCode ?: 'SA')),
            'prefDelvDate' => (string) ($metadata['pref_delv_date'] ?? ''),
            'gpsPoints' => (string) ($metadata['gps_points'] ?? ''),
        ];
    }
}
