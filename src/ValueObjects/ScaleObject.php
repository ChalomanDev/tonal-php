<?php

declare(strict_types=1);

namespace Chaloman\Tonal\ValueObjects;

/**
 * Scale representation
 */
final class ScaleObject
{
    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $tonic,
        public readonly int $setNum,
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
            'type' => $this->type,
            'tonic' => $this->tonic,
            'setNum' => $this->setNum,
            'chroma' => $this->chroma,
            'normalized' => $this->normalized,
            'aliases' => $this->aliases,
            'notes' => $this->notes,
            'intervals' => $this->intervals,
        ];
    }
}
