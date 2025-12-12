<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Duration values for musical notation
 *
 * @see https://en.wikipedia.org/wiki/Note_value
 */
final class DurationValue
{
    /**
     * Duration data: [denominator, shorthand, names[]]
     */
    private const array DATA = [
        [0.125, 'dl', ['large', 'duplex longa', 'maxima', 'octuple', 'octuple whole']],
        [0.25, 'l', ['long', 'longa']],
        [0.5, 'd', ['double whole', 'double', 'breve']],
        [1, 'w', ['whole', 'semibreve']],
        [2, 'h', ['half', 'minim']],
        [4, 'q', ['quarter', 'crotchet']],
        [8, 'e', ['eighth', 'quaver']],
        [16, 's', ['sixteenth', 'semiquaver']],
        [32, 't', ['thirty-second', 'demisemiquaver']],
        [64, 'sf', ['sixty-fourth', 'hemidemisemiquaver']],
        [128, 'h', ['hundred twenty-eighth']],
        [256, 'th', ['two hundred fifty-sixth']],
    ];

    /**
     * Cached base values
     * @var array<array{empty: bool, dots: string, name: string, value: float, fraction: array{0: int|float, 1: int|float}, shorthand: string, names: string[]}>
     */
    private static array $values = [];

    /**
     * Cache initialization flag
     */
    private static bool $initialized = false;

    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly float $value,
        /** @var array{0: int|float, 1: int|float} */
        public readonly array $fraction,
        public readonly string $shorthand,
        public readonly string $dots,
        /** @var string[] */
        public readonly array $names,
    ) {
    }

    /**
     * Initialize the values cache
     */
    private static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        foreach (self::DATA as [$denominator, $shorthand, $names]) {
            $value = 1 / $denominator;
            $fraction = $denominator < 1
                ? [1 / $denominator, 1]
                : [1, $denominator];

            self::$values[] = [
                'empty' => false,
                'dots' => '',
                'name' => '',
                'value' => $value,
                'fraction' => $fraction,
                'shorthand' => $shorthand,
                'names' => $names,
            ];
        }

        self::$initialized = true;
    }

    /**
     * Get all duration names
     *
     * @return string[]
     */
    public static function names(): array
    {
        self::init();

        $result = [];
        foreach (self::$values as $duration) {
            foreach ($duration['names'] as $name) {
                $result[] = $name;
            }
        }

        return $result;
    }

    /**
     * Get all duration shorthands
     *
     * @return string[]
     */
    public static function shorthands(): array
    {
        self::init();

        return array_map(fn ($dur) => $dur['shorthand'], self::$values);
    }

    /**
     * Get a duration value by name or shorthand
     */
    public static function get(string $name): self
    {
        self::init();

        if (!preg_match('/^([^.]+)(\.*)$/', $name, $matches)) {
            return self::empty();
        }

        $simple = $matches[1];
        $dots = $matches[2];

        $base = null;
        foreach (self::$values as $dur) {
            if ($dur['shorthand'] === $simple || in_array($simple, $dur['names'], true)) {
                $base = $dur;
                break;
            }
        }

        if ($base === null) {
            return self::empty();
        }

        $fraction = self::calcDots($base['fraction'], strlen($dots));
        $value = $fraction[0] / $fraction[1];

        return new self(
            empty: false,
            name: $name,
            value: $value,
            fraction: $fraction,
            shorthand: $base['shorthand'],
            dots: $dots,
            names: $base['names'],
        );
    }

    /**
     * Get the value of a duration
     */
    public static function value(string $name): float
    {
        return self::get($name)->value;
    }

    /**
     * Get the fraction of a duration
     *
     * @return array{0: int|float, 1: int|float}
     */
    public static function fraction(string $name): array
    {
        return self::get($name)->fraction;
    }

    /**
     * Create an empty duration value
     */
    private static function empty(): self
    {
        return new self(
            empty: true,
            name: '',
            value: 0,
            fraction: [0, 0],
            shorthand: '',
            dots: '',
            names: [],
        );
    }

    /**
     * Calculate the fraction with dots
     *
     * @param array{0: int|float, 1: int|float} $fraction
     * @return array{0: int|float, 1: int|float}
     */
    private static function calcDots(array $fraction, int $dots): array
    {
        $pow = pow(2, $dots);

        $numerator = $fraction[0] * $pow;
        $denominator = $fraction[1] * $pow;
        $base = $numerator;

        // Add fractions for each dot
        for ($i = 0; $i < $dots; $i++) {
            $numerator += $base / pow(2, $i + 1);
        }

        // Simplify the fraction
        while ($numerator > 0 && $denominator > 0 && fmod($numerator, 2) === 0.0 && fmod($denominator, 2) === 0.0) {
            $numerator /= 2;
            $denominator /= 2;
        }

        // Convert to int if possible
        $num = fmod($numerator, 1) === 0.0 ? (int) $numerator : $numerator;
        $den = fmod($denominator, 1) === 0.0 ? (int) $denominator : $denominator;

        return [$num, $den];
    }

    /**
     * Convert to array for testing/serialization
     *
     * @return array{empty: bool, name: string, value: float, fraction: array{0: int|float, 1: int|float}, shorthand: string, dots: string, names: string[]}
     */
    public function toArray(): array
    {
        return [
            'empty' => $this->empty,
            'name' => $this->name,
            'value' => $this->value,
            'fraction' => $this->fraction,
            'shorthand' => $this->shorthand,
            'dots' => $this->dots,
            'names' => $this->names,
        ];
    }
}
