<?php

declare(strict_types=1);


final class TaxEngine
{
    public function calculateTax(float $amount, float $rate): float
    {
        return round($amount * $rate, 2);
    }
}
