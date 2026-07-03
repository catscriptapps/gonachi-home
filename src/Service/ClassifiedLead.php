<?php
// /src/Service/ClassifiedLead.php

declare(strict_types=1);

namespace Src\Service;

final class ClassifiedLead
{
    public function __construct(
        public readonly string $requestType,
        public readonly ?string $propertyType,
        public readonly ?int $bedrooms,
        public readonly ?string $locationRaw,
        public readonly ?float $budgetMin,
        public readonly ?float $budgetMax,
        public readonly string $intentLevel,
    ) {
    }
}
