<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Time signature type
 */
enum TimeSignatureType: string
{
    case Simple = 'simple';
    case Compound = 'compound';
    case Irregular = 'irregular';
    case Irrational = 'irrational';
}

/**
 * Time signature representation
 */
final class TimeSignature
{
    /**
     * Common time signature names
     */
    private const array NAMES = ['4/4', '3/4', '2/4', '2/2', '12/8', '9/8', '6/8', '3/8'];

    /**
     * Pattern for parsing time signatures
     */
    private const string REGEX = '/^(\d+(?:\+\d+)*)\/(\d+)$/';

    /**
     * Cache for parsed time signatures
     * @var array<string, self>
     */
    private static array $cache = [];

    /**
     * @param int|array<int> $upper Upper part (beats per measure or additive)
     * @param array<int> $additive Additive parts (e.g., [2, 3, 3] for 2+3+3/8)
     */
    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly int|array|null $upper,
        public readonly ?int $lower,
        public readonly ?TimeSignatureType $type,
        public readonly array $additive,
    ) {}

    /**
     * Get common time signature names
     *
     * @return string[]
     */
    public static function names(): array
    {
        return self::NAMES;
    }

    /**
     * Get a time signature from a literal
     *
     * @param string|array{0: int|string, 1: int|string} $literal
     */
    public static function get(string|array $literal): self
    {
        $key = is_array($literal) ? json_encode($literal) : $literal;

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $parsed = self::parse($literal);
        $ts = self::build($parsed);

        self::$cache[$key] = $ts;

        return $ts;
    }

    /**
     * Parse a time signature literal
     *
     * @param string|array{0: int|string, 1: int|string} $literal
     * @return array{0: int|array<int>, 1: int}
     */
    public static function parse(string|array $literal): array
    {
        if (is_string($literal)) {
            if (!preg_match(self::REGEX, $literal, $matches)) {
                return [0, 0];
            }

            return self::parse([$matches[1], $matches[2]]);
        }

        [$up, $down] = $literal;
        $denominator = (int) $down;

        if (is_int($up)) {
            return [$up, $denominator];
        }

        $list = array_map('intval', explode('+', (string) $up));

        return count($list) === 1 ? [$list[0], $denominator] : [$list, $denominator];
    }

    /**
     * Build a time signature from parsed values
     *
     * @param array{0: int|array<int>, 1: int} $parsed
     */
    private static function build(array $parsed): self
    {
        [$up, $down] = $parsed;

        $upper = is_array($up) ? array_sum($up) : $up;
        $lower = $down;

        if ($upper === 0 || $lower === 0) {
            return self::empty();
        }

        $name = is_array($up)
            ? implode('+', $up) . '/' . $down
            : $up . '/' . $down;

        $additive = is_array($up) ? $up : [];

        $type = match (true) {
            $lower === 4 || $lower === 2 => TimeSignatureType::Simple,
            $lower === 8 && $upper % 3 === 0 => TimeSignatureType::Compound,
            self::isPowerOfTwo($lower) => TimeSignatureType::Irregular,
            default => TimeSignatureType::Irrational,
        };

        return new self(
            empty: false,
            name: $name,
            upper: $upper,
            lower: $lower,
            type: $type,
            additive: $additive,
        );
    }

    /**
     * Create an empty/invalid time signature
     */
    private static function empty(): self
    {
        return new self(
            empty: true,
            name: '',
            upper: null,
            lower: null,
            type: null,
            additive: [],
        );
    }

    /**
     * Check if a number is a power of two
     */
    private static function isPowerOfTwo(int $x): bool
    {
        if ($x <= 0) {
            return false;
        }

        return fmod(log($x) / log(2), 1) === 0.0;
    }

    /**
     * Convert to array for testing/serialization
     *
     * @return array{empty: bool, name: string, upper: int|array<int>|null, lower: ?int, type: ?string, additive: array<int>}
     */
    public function toArray(): array
    {
        return [
            'empty' => $this->empty,
            'name' => $this->name,
            'type' => $this->type?->value,
            'upper' => $this->upper,
            'lower' => $this->lower,
            'additive' => $this->additive,
        ];
    }
}
