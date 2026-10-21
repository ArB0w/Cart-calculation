# Cart Calculator

Basic PHP function for calculating final price of items in cart, including discount codes and shopping cost.

## Features

- Calculates the total from item prices and quantities.
- Supports the following discount codes:
  - `LETO10` – 10% discount on the subtotal.
  - `STOVKA` – 100 CZK discount for orders above 1,000 CZK.
  - `DOPRAVA` – free shipping.
- Adds standard shipping of 99 CZK when free shipping is not applied.
- Validates item prices and quantities.
- Uses integer arithmetic in cents to avoid floating-point precision issues like numbers 0.1 or acumulatice error.
- Supports floating-point prices too, but using integers to represent monetary values (in cents) is recommended to avoid floating-point precision issues.

## Requirements

- PHP 8.0 or later

## Usage

It is implemented as a static class to keep the code compact and make it easier to integrate into other systems.
If the input is invalid, the class throws an exception with a descriptive error message. This helps prevent invalid data from silently affecting the price calculation, which is especially important when handling monetary values.

Usage in case of integers:

```php
$items = [
    ['price' => 50000, 'quantity' => 2],
    ['price' => 20000, 'quantity' => 1],
];

$total = CartCalculator::calculateCost($items, 'STOVKA');

echo ($total / 100) . ' CZK'; // 1199 CZK
```

Prices are specified in cents. The method returns the final price in cents as an integer.

---

Usage in case of floats:

```php
$items = [
    ['price' => 500.50, 'quantity' => 2],
    ['price' => 200.75, 'quantity' => 1],
];

$total = CartCalculator::calculateCostFromFloat($items, 'STOVKA');

echo number_format($total / 100, 2, '.', '') . ' CZK'; // 1101.75 CZK
```

Floating-point prices are supported, but may introduce precision and rounding issues. Using integers to represent monetary values in cents is recommended.

## Running Tests

The project also includes 13 basic tests to verify the correct functionality of the class. These tests cover the main use cases but do not include edge cases or extensive input validation testing.

First install dependenceis like PHPUnit

```bash
composer install
```

If PHPUnit is installed through Composer, it can be used vith command:

```bash
vendor/bin/phpunit tests/CartTest.php
```

## Assumptions

- The `STOVKA` discount applies only when the subtotal exceeds 1,000 CZK.
- Discount codes are case-insensitive.
- Unknown discount codes throw error.
- Shipping is added after the discount is applied.
- Item prices must be non-negative integers, and quantities must be positive integers.
