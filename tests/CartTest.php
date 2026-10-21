<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Cart.php';

class CartTest extends TestCase
{
    public function testCalculateCost(): void
    {
        $items = [
            ['price' => 10000, 'quantity' => 2],
            ['price' => 100000, 'quantity' => 2],
        ];

        // 2200 Kč za zboží + 99 Kč doprava
        self::assertSame(229900, Cart::calculateCost($items));
    }

    public function testCalculateCostWithLETO10(): void
    {
        $items = [
            ['price' => 100000, 'quantity' => 1],
        ];

        // 1 000 Kč - 10 % + 99 Kč doprava
        self::assertSame(99900, Cart::calculateCost($items, 'LETO10'));
    }

    public function testCalculateCostWithSTOVKAAboveThreshold(): void
    {
        $items = [
            ['price' => 100001, 'quantity' => 1],
        ];

        // 1 000,01 Kč - 100 Kč + 99 Kč doprava
        self::assertSame(99901, Cart::calculateCost($items, 'STOVKA'));
    }

    public function testSTOVKADoesNotApplyAtThreshold(): void
    {
        $items = [
            ['price' => 100000, 'quantity' => 1],
        ];

        // Přesně 1 000 Kč: sleva se neuplatní
        self::assertSame(109900, Cart::calculateCost($items, 'STOVKA'));
    }

    public function testDOPRAVA(): void
    {
        $items = [
            ['price' => 10000, 'quantity' => 1],
        ];

        // 100 Kč, doprava zdarma
        self::assertSame(10000, Cart::calculateCost($items, 'DOPRAVA'));
    }

    public function testCalculateCostFromFloat(): void
    {
        $items = [
            ['price' => 100.50, 'quantity' => 2],
        ];

        // 201 Kč + 99 Kč doprava
        self::assertSame(300.0, Cart::calculateCostFromFloat($items));
    }

    public function testCalculateCostFromFloatWithLETO10(): void
    {
        $items = [
            ['price' => 1000.00, 'quantity' => 1],
        ];

        // 1 000 Kč - 100 Kč + 99 Kč doprava
        self::assertSame(999.0, Cart::calculateCostFromFloat($items, 'LETO10'));
    }

    public function testNegativePriceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Cart::calculateCost([
            ['price' => -100, 'quantity' => 1],
        ]);
    }

    public function testFloatPriceIsRejectedByCalculateCost(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Cart::calculateCost([
            ['price' => 100.50, 'quantity' => 1],
        ]);
    }

    public function testNegativeQuantityThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Cart::calculateCost([
            ['price' => 10000, 'quantity' => -1],
        ]);
    }

    public function testUnknownDiscountCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Cart::calculateCost([
            ['price' => 10000, 'quantity' => 1],
        ], 'NEEXISTUJE');
    }

    public function testNegativeFloatPriceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Cart::calculateCostFromFloat([
            ['price' => -10.50, 'quantity' => 1],
        ]);
    }

    public function testFloatPriceIsConvertedToIntegerCents(): void
    {
        $items = [
            ['price' => 125.50, 'quantity' => 2],
        ];

        $transformedItems = Cart::validateAndTransformeItemsFromFloat($items);

        self::assertSame([
            ['price' => 12550, 'quantity' => 2],
        ], $transformedItems);
    }
}
