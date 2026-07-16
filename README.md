# ShipBridge · SMSA Express


[![CI](https://github.com/mohamedhekal/shipbridge-smsa/actions/workflows/tests.yml/badge.svg)](https://github.com/mohamedhekal/shipbridge-smsa/actions)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Packagist](https://img.shields.io/packagist/v/mohamedhekal/shipbridge-smsa.svg)](https://packagist.org/packages/mohamedhekal/shipbridge-smsa)

**SMSA Express** shipping driver for [ShipBridge](https://github.com/mohamedhekal/shipbridge) · Region: **Saudi Arabia / GCC** / **السعودية والخليج**

Real SECOM SOAP API: `SMSAwebService.asmx`

---

## بالعربي — في ٣ خطوات

### ١) ثبّت الحزمتين
```bash
composer require mohamedhekal/shipbridge mohamedhekal/shipbridge-smsa
```

### ٢) حط مفتاح SMSA في `.env`
```env
SHIPBRIDGE_DRIVER=smsa
SMSA_PASSKEY=your-passkey
SMSA_WSDL=http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?WSDL
```
> التفاصيل في [`docs/GUIDE_AR.md`](docs/GUIDE_AR.md).

### ٣) ابعت شحنة
```php
use Hekal\ShipBridge\Facades\ShipBridge;
use Hekal\ShipBridge\DTOs\Address;
use Hekal\ShipBridge\DTOs\CreateShipmentRequest;
use Hekal\ShipBridge\DTOs\Parcel;

$shipment = ShipBridge::driver('smsa')->createShipment(new CreateShipmentRequest(
    origin: new Address('المستودع', 'شارع الملك', 'Riyadh', 'SA', phone: '0110000000'),
    destination: new Address('العميل', 'طريق فهد', 'Jeddah', 'SA', phone: '0555555555'),
    parcels: [new Parcel(weightKg: 2.0, description: 'ملابس')],
    reference: 'ORD-42',
    metadata: ['cod' => 50],
));

echo $shipment->trackingNumber; // AWB
```

---

## English — Quick start

```bash
composer require mohamedhekal/shipbridge mohamedhekal/shipbridge-smsa
```

```env
SHIPBRIDGE_DRIVER=smsa
SMSA_PASSKEY=your-passkey
```

```php
ShipBridge::driver('smsa')->createShipment(...); // addShip
ShipBridge::driver('smsa')->track('AWB');         // getTrack
ShipBridge::driver('smsa')->label('AWB');         // getPDF
```

Requires PHP `ext-soap`. See [`docs/API.md`](docs/API.md).

## Testing

```bash
composer install && composer test
```

## License

MIT © Mohamed Hekal
