<?php

declare(strict_types=1);

require_once __DIR__ . '/PricingModel.php';


final class PricingEngine
{
    public function calculatePrice(float $duration, float $rate): float
    {
        return round($duration * $rate, 2);
    }

    
    public function calculatePriceWithPeak(float $baseBeforePeak, string $startTime): array
    {
        return (new PricingModel())->calculatePeakPrice($baseBeforePeak, $startTime);
    }
}
