# ShipBridge · SMSA Express


[![CI](https://github.com/mohamedhekal/shipbridge-smsa/actions/workflows/tests.yml/badge.svg)](https://github.com/mohamedhekal/shipbridge-smsa/actions)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Packagist](https://img.shields.io/packagist/v/mohamedhekal/shipbridge-smsa.svg)](https://packagist.org/packages/mohamedhekal/shipbridge-smsa)

**SMSA Express** shipping driver for [ShipBridge](https://github.com/mohamedhekal/shipbridge) · Region: **Saudi Arabia / GCC** / **السعودية والخليج**

---

## بالعربي — في ٣ خطوات

### ١) ثبّت الحزمتين
```bash
composer require mohamedhekal/shipbridge mohamedhekal/shipbridge-smsa
```

### ٢) حط مفاتيح SMSA Express في `.env`
```env
SHIPBRIDGE_DRIVER=smsa
SMSA_API_KEY=your-key-here
SMSA_BASE_URL=https://track.smsaexpress.com/SecomRestWebApi/api
```
> لو الشركة بتستخدم username/password أو OAuth، شوف ملف `config/smsa.php`.

### ٣) ابعت شحنة
```php
use Hekal\ShipBridge\Facades\ShipBridge;
use Hekal\ShipBridge\DTOs\Address;
use Hekal\ShipBridge\DTOs\CreateShipmentRequest;
use Hekal\ShipBridge\DTOs\Parcel;

$shipment = ShipBridge::driver('smsa')->createShipment(new CreateShipmentRequest(
    origin: new Address('المخزن', 'شارع ١', 'القاهرة', 'EG'),
    destination: new Address('العميل', 'شارع النيل', 'الجيزة', 'EG', phone: '01000000000'),
    parcels: [new Parcel(weightKg: 1.2)],
    reference: 'ORD-42',
));

echo $shipment->trackingNumber;
```

تتبع / ليبل / مرتجع:
```php
ShipBridge::driver('smsa')->track($shipment->trackingNumber);
ShipBridge::driver('smsa')->label($shipment->id);
```

---

## English — Quick start

```bash
composer require mohamedhekal/shipbridge mohamedhekal/shipbridge-smsa
```

```env
SHIPBRIDGE_DRIVER=smsa
SMSA_API_KEY=your-key-here
```

```php
ShipBridge::driver('smsa')->createShipment(...);
ShipBridge::driver('smsa')->track('TRACKING');
ShipBridge::driver('smsa')->label('SHIPMENT_ID');
```

## How it fits

```
Your Laravel app
      │
      ▼
 ShipBridge  (one API for all carriers)
      │
      ▼
 shipbridge-smsa  ← this package (SMSA Express)
```

## Testing

```bash
composer install && composer test
```

## License

MIT © Mohamed Hekal
