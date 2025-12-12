<?php

declare(strict_types=1);

namespace Chaloman\Tonal\ValueObjects;

/**
 * Major key representation
 */
final class MajorKey
{
    public function __construct(
        public readonly string $type,
        public readonly string $tonic,
        public readonly int $alteration,
        public readonly string $keySignature,
        public readonly string $minorRelative,
        /** @var array<string> */
        public readonly array $grades,
        /** @var array<string> */
        public readonly array $intervals,
        /** @var array<string> */
        public readonly array $scale,
        /** @var array<string> */
        public readonly array $triads,
        /** @var array<string> */
        public readonly array $chords,
        /** @var array<string> */
        public readonly array $chordsHarmonicFunction,
        /** @var array<string> */
        public readonly array $chordScales,
        /** @var array<string> */
        public readonly array $secondaryDominants,
        /** @var array<string> */
        public readonly array $secondaryDominantSupertonics,
        /** @var array<string> */
        public readonly array $substituteDominants,
        /** @var array<string> */
        public readonly array $substituteDominantSupertonics,
    ) {
    }

    /**
     * @deprecated use secondaryDominantSupertonics
     * @return array<string>
     */
    public function secondaryDominantsMinorRelative(): array
    {
        return $this->secondaryDominantSupertonics;
    }

    /**
     * @deprecated use substituteDominantSupertonics
     * @return array<string>
     */
    public function substituteDominantsMinorRelative(): array
    {
        return $this->substituteDominantSupertonics;
    }
}
