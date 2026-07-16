# دليل SMSA Express — شرح بسيط ومفصّل

## إيه هي الحزمة دي؟

`mohamedhekal/shipbridge-smsa` تربط Laravel بـ **SMSA Express** (السعودية والخليج) عن طريق ShipBridge و SOAP SECOM.

```
تطبيقك → ShipBridge → shipbridge-smsa → SMSA SOAP
```

---

## قبل ما تبدأ

1. اطلب **passKey** من SMSA Express
2. للاختبار غالبًا بيستخدموا `Testing0` (حسب عقدك مع SMSA)
3. تأكد إن `ext-soap` مفعّل في PHP

---

## التثبيت

```bash
composer require mohamedhekal/shipbridge mohamedhekal/shipbridge-smsa
```

`.env`:

```env
SHIPBRIDGE_DRIVER=smsa
SMSA_PASSKEY=your-passkey
SMSA_WSDL=http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?WSDL
```

---

## ابعت شحنة (COD)

```php
use Hekal\ShipBridge\Facades\ShipBridge;
use Hekal\ShipBridge\DTOs\Address;
use Hekal\ShipBridge\DTOs\CreateShipmentRequest;
use Hekal\ShipBridge\DTOs\Parcel;

$shipment = ShipBridge::driver('smsa')->createShipment(new CreateShipmentRequest(
    origin: new Address('المستودع', 'شارع الملك', 'Riyadh', 'SA', phone: '0110000000'),
    destination: new Address('العميل', 'طريق فهد', 'Jeddah', 'SA', phone: '0555555555'),
    parcels: [new Parcel(weightKg: 2.0, description: 'ملابس')],
    reference: 'ORD-1001',
    metadata: [
        'cod' => 50,
    ],
));

$shipment->trackingNumber; // AWB
```

---

## تتبع / بوليصة PDF / مرتجع

```php
ShipBridge::driver('smsa')->track($shipment->trackingNumber); // getTrack (+ getStatus fallback)
ShipBridge::driver('smsa')->label($shipment->trackingNumber); // getPDF
ShipBridge::driver('smsa')->createReturn(...); // shipType=RET
```

---

## ملاحظات

| البند | التفاصيل |
|---|---|
| API | SOAP SECOM (`addShip` / `getTrack` / `getStatus` / `getPDF`) |
| Auth | `passKey` |
| مدن | أسماء المدن غالبًا بالإنجليزي uppercase زي `JEDDAH` |
| COD | `codAmt` من `metadata.cod` |
