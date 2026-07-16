# SMSA Express API reference

SECOM SOAP Web Service used by SMSA Express (KSA / GCC).

## WSDL

```
http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?WSDL
```

Override with `SMSA_WSDL`.

## Auth

Every operation includes `passKey` / `passkey` provided by SMSA.

## Operations used by this package

| Action | SOAP method |
|---|---|
| Create | `addShip` |
| Track | `getTrack` (fallback `getStatus`) |
| Label | `getPDF` |
| Cancel | `cancelShipment` (via gateway helper) |

## addShip key fields

| Field | Meaning |
|---|---|
| `refNo` | Your reference |
| `cName` / `cMobile` / `cCity` / `cAddr1` | Consignee |
| `cntry` | Country (`SA`, …) |
| `shipType` | `DLV`, `RET`, … |
| `PCs` | Pieces |
| `codAmt` | Cash on delivery |
| `weight` | Weight |
| `sName` / `sAddr1` / `sCity` / `sPhone` / `sCntry` | Shipper |

Success returns AWB number string (e.g. `290019315792`).
Error strings containing `Failed` / `Invalid` raise `ShipBridgeException`.

## getPDF

Returns base64 PDF contents for the AWB.
