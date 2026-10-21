<?php


/**
 * Static class that implements Counting in cart
 */
class Cart
{
    /** 
     * Default cost of shopping in cents.
     */
    public const SHIPPING_COST = 9900;

    /**
     * Calculates the total cart cost, including discounts and shipping in cents.
     * 1,5 Kč = 140
     * 
     * @param list<array{price: int, quantity: int}> $items Items to count in cents
     * @param string|null $saleCode Discount 
     * @return int Total cost in cents
     */
    public static function calculateCost(array $items, ?string $saleCode = null): int
    {
        self::validateInput($items);

        $total = self::calculateTotal($items);
        $total -= self::calculateSale($total, $saleCode);
        $total += self::calculateShipping($saleCode);

        return $total;
    }


    /**
     * Validates that all cart items have the expected data types and valid values.
     * 
     * @param list<array{price: int, quantity: int}> $items Items to validate
     */
    private static function validateInput(array $items): void
    {
        foreach ($items as $index => $item) {

            // Validates attribute price - Item has this attribute, is integer and is greater than zero.
            if (!isset($item['price']) || !is_int($item['price']) || $item['price'] <= 0) {
                throw new \InvalidArgumentException(
                    "Item at index {$index}: price must be a non-negative integer."
                );
            }

            // Validates attribute quantity - Item has this attribute, is integer and is greater than zero.
            if (!isset($item['quantity']) || !is_int($item['quantity']) || $item['quantity'] <= 0) {
                throw new \InvalidArgumentException(
                    "Item at index {$index}: quantity must be a non-negative integer."
                );
            }
        }
    }
    /**
     * Calculates the total cost of all items in cents.
     * 
     * @param list<array{price: int, quantity: int}> $items Items to count in cents
     * @return int Total cost in cents
     */
    private static function calculateTotal(array $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    /**
     * Calculates the shipping cost in cents based on the discount code.
     * 
     * @param string|null $saleCode Discount code
     * @return int Total shipping cost in cents
     */
    private static function calculateShipping(?string $saleCode): int
    {
        return $saleCode === 'DOPRAVA' ? 0 : self::SHIPPING_COST;
    }


    /**
     * Calculate sale by sale code in cents. If it is an unknown code, throws exception. If it is more than total, set to total.
     * 
     * @param int $total Total cost
     * @param string|null $saleCode Discount code
     * @return int Total seles in cents
     */
    private static function calculateSale(int $total, ?string $saleCode): int
    {
        $sale = 0;
        switch ($saleCode) {

            // No sale code or it's already happened.
            case null:
            case 'DOPRAVA':
                $sale = 0;
                break;

            // Apply discount LETO10
            case 'LETO10':
                $sale =  (int) round($total / 10);
                break;

            // Apply discount STOVKA
            case 'STOVKA':
                $sale =  $total > 100000 ? 10000 : 0;
                break;

            // Unknown sale code
            default:
                throw new \InvalidArgumentException(
                    "Unknown discount code: {$saleCode}"
                );
        }
        // Set maximum sale.
        return min($sale, $total);
    }

    /**
     * Calculates the total cart cost. Same as function calculateCost(), but it uses float, like '1.40' Kč.
     * 
     * 
     * @param list<array{price: int|float, quantity: int}> $items Items to count
     * @param string|null $saleCode Discount 
     * @return float Total cost 
     */
    public static function calculateCostFromFloat(
        array $items,
        ?string $saleCode = null
    ): float {
        $validatedAndTransformedItems = self::validateAndTransformeItemsFromFloat($items);
        return self::calculateCost($validatedAndTransformedItems, $saleCode) / 100;
    }

    /**
     * Validates and transforms input in type float to integer and converts them into cents.
     * 
     * @param list<array{price: float, quantity: int}> $items Items in fload
     * @return list<array{price: int, quantity: int}> $items Items in int in cents
     */
    public static function validateAndTransformeItemsFromFloat(
        array $items
    ): array {
        $validatedAndTransformedItems = [];

        foreach ($items as $index => $item) {

            // Control if it has this attribute and if it is numeric. 
            if (!isset($item['price']) || !is_numeric($item['price'])) {
                throw new \InvalidArgumentException(
                    "Item at index {$index}: price must be numeric."
                );
            }

            //Transform it in float if it is not
            $price = (float) $item['price'];

            // Control if it is correct value
            if (!is_finite($price) || $price < 0) {
                throw new \InvalidArgumentException(
                    "Item at index {$index}: price must be finite and non-negative."
                );
            }

            // Transform it in int in cents.
            $validatedAndTransformedItems[] = [
                'price' => (int) round($price * 100),
                'quantity' => $item['quantity'],
            ];
        }

        return $validatedAndTransformedItems;
    }
}
