<?php

declare(strict_types=1);

namespace BitBag\ShopwareInPostPlugin\Calculator;

use BitBag\ShopwareInPostPlugin\Exception\OrderException;
use Shopware\Core\Checkout\Order\OrderEntity;

final class OrderWeightCalculator implements OrderWeightCalculatorInterface
{
    public function calculate(OrderEntity $order): float
    {
        $totalWeight = 0.0;

        $orderLineItems = $order->getLineItems();

        if (null === $orderLineItems) {
            throw new OrderException('order.productsNotFound');
        }

        foreach ($orderLineItems->getElements() as $item) {
            $product = $item->getProduct();

            if ($product) {
                $weight = $item->getQuantity() * $product->getWeight();
                $totalWeight += $weight;
            }
        }

        return $totalWeight;
    }
}