<?php

declare(strict_types=1);

namespace Chaloman\Tonal\ValueObjects;

/**
 * Minor key representation
 */
final class MinorKey
{
    public function __construct(
        public readonly string $type,
        public readonly string $tonic,
        public readonly int $alteration,
        public readonly string $keySignature,
        public readonly string $relativeMajor,
        public readonly KeyScale $natural,
        public readonly KeyScale $harmonic,
        public readonly KeyScale $melodic,
    ) {
    }
}
