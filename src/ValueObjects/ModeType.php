<?php

declare(strict_types=1);

namespace Chaloman\Tonal\ValueObjects;

/**
 * Mode representation
 */
final class ModeType
{
    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly int $modeNum,
        public readonly int $setNum,
        public readonly string $chroma,
        public readonly string $normalized,
        public readonly int $alt,
        public readonly string $triad,
        public readonly string $seventh,
        /** @var array<string> */
        public readonly array $aliases,
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
            'modeNum' => $this->modeNum,
            'setNum' => $this->setNum,
            'chroma' => $this->chroma,
            'normalized' => $this->normalized,
            'alt' => $this->alt,
            'triad' => $this->triad,
            'seventh' => $this->seventh,
            'aliases' => $this->aliases,
            'intervals' => $this->intervals,
        ];
    }
}
