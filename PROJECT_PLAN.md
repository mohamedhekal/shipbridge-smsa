# ShipBridge SMSA Express — Plan

## Package
`mohamedhekal/shipbridge-smsa`

## Role
Carrier driver for **SMSA Express** (Saudi Arabia / GCC) on top of `mohamedhekal/shipbridge`.

## v0.1
- Implement `CarrierDriver`
- Auto-register via Laravel package discovery
- Config + status map
- Http::fake Pest tests

## Later
- Vendor-specific payload quirks
- Webhook signature verification
- Live sandbox integration tests (optional, gated by env)
