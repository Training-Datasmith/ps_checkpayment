# Architecture: ps_checkpayment

## Purpose

A PrestaShop payment module that adds a "Payment by Check" option at checkout. Customers
select this option and are shown the store's banking/check details. No online payment
processing occurs; the order is held until the check is received.

## Directory Structure

```
ps_checkpayment.php   # Main module class (PaymentModule)
views/templates/       # Confirmation and payment info templates
translations/          # Translation files
tests/                 # PHPStan and unit tests
```

## Key Design Decisions

Extends `PaymentModule`. Stores payee name and address in module configuration.
The `hookPaymentOptions()` method returns a `PaymentOption` with check-specific display
and a confirmation template showing check instructions.

## Extension Points

Configure payee details in the module's back-office settings panel.
