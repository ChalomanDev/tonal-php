<?php

declare(strict_types=1);

namespace Chaloman\Tonal\Contracts;

/**
 * Interface for named pitch objects
 */
interface NamedPitchInterface
{
    public function getName(): string;
}
