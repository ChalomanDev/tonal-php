<?php

declare(strict_types=1);

namespace Chaloman\Tonal\ValueObjects;

/**
 * Chord representation
 */
final class ChordObject
{
    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly string $symbol,
        public readonly ?string $tonic,
        public readonly string $root,
        public readonly string $bass,
        public readonly int|float $rootDegree,
        public readonly string $type,
        public readonly int $setNum,
        public readonly string $quality,
        public readonly string $chroma,
        public readonly string $normalized,
        /** @var array<string> */
        public readonly array $aliases,
        /** @var array<string> */
        public readonly array $notes,
        /** @var array<string> */
        public readonly array $intervals,
    ) {
    }

    /**
     * Convert to array for testing/serialization
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'empty' => $this->empty,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'tonic' => $this->tonic,
            'root' => $this->root,
            'bass' => $this->bass,
            'rootDegree' => $this->rootDegree,
            'type' => $this->type,
            'setNum' => $this->setNum,
            'quality' => $this->quality,
            'chroma' => $this->chroma,
            'normalized' => $this->normalized,
            'aliases' => $this->aliases,
            'notes' => $this->notes,
            'intervals' => $this->intervals,
        ];
    }
}
