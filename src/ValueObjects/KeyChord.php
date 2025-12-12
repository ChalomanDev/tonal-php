<?php

declare(strict_types=1);

namespace Chaloman\Tonal\ValueObjects;

/**
 * Key chord with roles
 */
final class KeyChord
{
    /**
     * @param array<string> $roles
     */
    public function __construct(
        public readonly string $name,
        public array $roles = [],
    ) {
    }
}
