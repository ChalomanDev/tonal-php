<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

use Chaloman\Tonal\Contracts\NamedPitchInterface;
use Chaloman\Tonal\Enums\Direction;

/**
 * Pitch properties
 *
 * - step: The step number: 0 = C, 1 = D, ... 6 = B
 * - alt: Number of alterations: -2 = 'bb', -1 = 'b', 0 = '', 1 = '#', ...
 * - oct: The octave (null when is a pitch class)
 * - dir: Interval direction (null when is not an interval)
 */
final class Pitch
{
    /**
     * Semitone sizes for each step [C, D, E, F, G, A, B]
     */
    private const array SIZES = [0, 2, 4, 5, 7, 9, 11];

    /**
     * The number of fifths of [C, D, E, F, G, A, B]
     */
    private const array FIFTHS = [0, 2, 4, -1, 1, 3, 5];

    /**
     * Steps to fifths mapping [F, C, G, D, A, E, B]
     */
    private const array FIFTHS_TO_STEPS = [3, 0, 4, 1, 5, 2, 6];

    /**
     * The number of octaves each step spans
     * @var array<int, int>
     */
    private static array $stepsToOcts;

    public function __construct(
        public readonly int $step,
        public readonly int $alt,
        public readonly ?int $oct = null,
        public readonly ?Direction $dir = null,
    ) {
        self::initStepsToOcts();
    }

    /**
     * Initialize the steps to octaves mapping (lazy loaded)
     */
    private static function initStepsToOcts(): void
    {
        if (!isset(self::$stepsToOcts)) {
            self::$stepsToOcts = array_map(
                fn (int $fifths): int => (int) floor(($fifths * 7) / 12),
                self::FIFTHS,
            );
        }
    }

    /**
     * Check if a value is a valid NamedPitch
     */
    public static function isNamedPitch(mixed $src): bool
    {
        return $src instanceof NamedPitchInterface;
    }

    /**
     * Check if a value is a valid Pitch
     */
    public static function isPitch(mixed $pitch): bool
    {
        return $pitch instanceof self;
    }

    /**
     * Calculate the chroma (0-11) of a pitch
     */
    public static function chroma(self $pitch): int
    {
        return (self::SIZES[$pitch->step] + $pitch->alt + 120) % 12;
    }

    /**
     * Calculate the height of a pitch
     */
    public static function height(self $pitch): int
    {
        $dir = $pitch->dir !== null ? $pitch->dir->value : 1;
        $oct = $pitch->oct ?? -100;

        return $dir * (self::SIZES[$pitch->step] + $pitch->alt + 12 * $oct);
    }

    /**
     * Calculate the MIDI number of a pitch (or null if not applicable)
     */
    public static function midi(self $pitch): ?int
    {
        $h = self::height($pitch);

        if ($pitch->oct !== null && $h >= -12 && $h <= 115) {
            return $h + 12;
        }

        return null;
    }

    /**
     * Get coordinates from pitch object
     *
     * @return array{0: int}|array{0: int, 1: int}
     */
    public static function coordinates(self $pitch): array
    {
        self::initStepsToOcts();

        $dir = $pitch->dir !== null ? $pitch->dir->value : 1;
        $f = self::FIFTHS[$pitch->step] + 7 * $pitch->alt;

        if ($pitch->oct === null) {
            return [$dir * $f];
        }

        $o = $pitch->oct - self::$stepsToOcts[$pitch->step] - 4 * $pitch->alt;

        return [$dir * $f, $dir * $o];
    }

    /**
     * Get pitch from coordinate array
     *
     * @param array{0: int, 1?: int, 2?: int} $coord
     */
    public static function fromCoordinates(array $coord): self
    {
        self::initStepsToOcts();

        $f = $coord[0];
        $o = $coord[1] ?? null;
        $dirValue = $coord[2] ?? null;

        $dir = match ($dirValue) {
            1 => Direction::Ascending,
            -1 => Direction::Descending,
            default => null,
        };

        $step = self::FIFTHS_TO_STEPS[self::unaltered($f)];
        $alt = (int) floor(($f + 1) / 7);

        if ($o === null) {
            return new self($step, $alt, null, $dir);
        }

        $oct = $o + 4 * $alt + self::$stepsToOcts[$step];

        return new self($step, $alt, $oct, $dir);
    }

    /**
     * Return the number of fifths as if it were unaltered
     */
    private static function unaltered(int $f): int
    {
        $i = ($f + 1) % 7;

        return $i < 0 ? 7 + $i : $i;
    }

    /**
     * Convert to array for testing/serialization
     *
     * @return array{step: int, alt: int, oct?: int, dir?: int}
     */
    public function toArray(): array
    {
        $result = [
            'step' => $this->step,
            'alt' => $this->alt,
        ];

        if ($this->oct !== null) {
            $result['oct'] = $this->oct;
        }

        if ($this->dir !== null) {
            $result['dir'] = $this->dir->value;
        }

        return $result;
    }
}
