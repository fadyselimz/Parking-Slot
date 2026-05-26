<?php

declare(strict_types=1);


final class PromoCode
{
    public function __construct(
        public string $codeID,
        public string $code,
        public float $discountAmount,
        public string $expiryDate,
    ) {
    }

    
    public static function fromDatabaseRow(array $row): self
    {
        $amount = $row['discount_type'] === 'percentage'
            ? (float)($row['discount_value'] ?? 0)
            : (float)($row['discount_value'] ?? 0);

        return new self(
            (string)($row['code_id'] ?? ''),
            (string)($row['code'] ?? ''),
            $amount,
            (string)($row['expiry_date'] ?? ''),
        );
    }
}
