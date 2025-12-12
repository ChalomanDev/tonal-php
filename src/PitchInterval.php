<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

use Chaloman\Tonal\Contracts\PitchLike;
use Chaloman\Tonal\Enums\Direction;
use Chaloman\Tonal\Enums\IntervalType;

/**
 * Musical interval representation
 */
final class PitchInterval
{
    /**
     * Semitone sizes for steps
     */
    private const array SIZES = [0, 2, 4, 5, 7, 9, 11];

    /**
     * Type for each step (P=perfectable, M=majorable)
     */
    private const string TYPES = 'PMMPPMM';

    /**
     * Regex for tonal notation (number before quality): "5P", "-3m"
     */
    private const string INTERVAL_TONAL_REGEX = '([-+]?\d+)(d{1,4}|m|M|P|A{1,4})';

    /**
     * Regex for shorthand notation (quality before number): "P5", "m-3"
     */
    private const string INTERVAL_SHORTHAND_REGEX = '(AA|A|P|M|m|d|dd)([-+]?\d+)';

    /**
     * Combined regex pattern
     */
    private const string REGEX = '/^' . self::INTERVAL_TONAL_REGEX . '|' . self::INTERVAL_SHORTHAND_REGEX . '$/';

    /**
     * Cache for parsed intervals
     * @var array<string, self>
     */
    private static array $cache = [];

    /**
     * @param array{}|array{0: int, 1: int} $coord Interval coordinates [fifths, octaves] (empty for invalid intervals)
     */
    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly int $num,
        public readonly string $q,
        public readonly ?IntervalType $type,
        public readonly int $step,
        public readonly int $alt,
        public readonly int $dir,
        public readonly int $simple,
        public readonly int $semitones,
        public readonly int $chroma,
        public readonly array $coord,
        public readonly int $oct,
    ) {
    }

    /**
     * Get interval properties from a string, Pitch, or PitchLike object
     */
    public static function interval(string|Pitch|PitchLike $src): self
    {
        if (is_string($src)) {
            if (isset(self::$cache[$src])) {
                return self::$cache[$src];
            }

            $interval = self::parse($src);
            self::$cache[$src] = $interval;

            return $interval;
        }

        // If it's a Pitch object
        if ($src instanceof Pitch) {
            return self::interval(self::pitchName($src));
        }

        // If it's a PitchLike object (e.g., RomanNumeral)
        $dir = $src->getDir() === 1 ? Direction::Ascending : Direction::Descending;
        $pitch = new Pitch($src->getStep(), $src->getAlt(), $src->getOct(), $dir);

        return self::interval(self::pitchName($pitch));
    }

    /**
     * Alias for interval()
     */
    public static function get(string|Pitch|PitchLike $src): self
    {
        return self::interval($src);
    }

    /**
     * Tokenize an interval string
     *
     * @return array{0: string, 1: string}
     */
    public static function tokenizeInterval(string $str): array
    {
        if (!preg_match(self::REGEX, $str, $m)) {
            return ['', ''];
        }

        // Tonal notation (num+quality): $m[1] = num, $m[2] = quality
        // Shorthand notation (quality+num): $m[3] = quality, $m[4] = num
        return !empty($m[1]) ? [$m[1], $m[2]] : [$m[4], $m[3]];
    }

    /**
     * Convert coordinates to interval
     *
     * @param array{0: int, 1?: int} $coord
     */
    public static function coordToInterval(array $coord, bool $forceDescending = false): self
    {
        $f = $coord[0];
        $o = $coord[1] ?? 0;

        $isDescending = $f * 7 + $o * 12 < 0;
        $ivl = ($forceDescending || $isDescending)
            ? [-$f, -$o, -1]
            : [$f, $o, 1];

        $pitch = Pitch::fromCoordinates($ivl);

        return self::interval($pitch);
    }

    /**
     * Parse an interval string
     */
    private static function parse(string $str): self
    {
        $tokens = self::tokenizeInterval($str);

        if ($tokens[0] === '') {
            return self::empty();
        }

        $num = (int) $tokens[0];
        $q = $tokens[1];

        $step = (abs($num) - 1) % 7;
        $t = self::TYPES[$step];

        // Invalid: majorable interval with Perfect quality
        if ($t === 'M' && $q === 'P') {
            return self::empty();
        }

        $type = $t === 'M' ? IntervalType::Majorable : IntervalType::Perfectable;
        $name = $num . $q;
        $dir = $num < 0 ? -1 : 1;
        $simple = ($num === 8 || $num === -8) ? $num : $dir * ($step + 1);
        $alt = self::qToAlt($type, $q);
        $oct = (int) floor((abs($num) - 1) / 7);
        $semitones = $dir * (self::SIZES[$step] + $alt + 12 * $oct);
        $chroma = (((($dir * (self::SIZES[$step] + $alt)) % 12) + 12) % 12);

        $pitch = new Pitch($step, $alt, $oct, $dir === 1 ? Direction::Ascending : Direction::Descending);
        $coord = Pitch::coordinates($pitch);

        return new self(
            empty: false,
            name: $name,
            num: $num,
            q: $q,
            type: $type,
            step: $step,
            alt: $alt,
            dir: $dir,
            simple: $simple,
            semitones: $semitones,
            chroma: $chroma,
            coord: $coord,
            oct: $oct,
        );
    }

    /**
     * Create empty interval
     */
    private static function empty(): self
    {
        return new self(
            empty: true,
            name: '',
            num: 0,
            q: '',
            type: null,
            step: 0,
            alt: 0,
            dir: 0,
            simple: 0,
            semitones: 0,
            chroma: 0,
            coord: [],
            oct: 0,
        );
    }

    /**
     * Convert quality to alteration
     */
    private static function qToAlt(IntervalType $type, string $q): int
    {
        if (($q === 'M' && $type === IntervalType::Majorable) ||
            ($q === 'P' && $type === IntervalType::Perfectable)) {
            return 0;
        }

        if ($q === 'm' && $type === IntervalType::Majorable) {
            return -1;
        }

        if (preg_match('/^A+$/', $q)) {
            return strlen($q);
        }

        if (preg_match('/^d+$/', $q)) {
            return -1 * ($type === IntervalType::Perfectable ? strlen($q) : strlen($q) + 1);
        }

        return 0;
    }

    /**
     * Convert alteration to quality
     */
    private static function altToQ(IntervalType $type, int $alt): string
    {
        if ($alt === 0) {
            return $type === IntervalType::Majorable ? 'M' : 'P';
        }

        if ($alt === -1 && $type === IntervalType::Majorable) {
            return 'm';
        }

        if ($alt > 0) {
            return str_repeat('A', $alt);
        }

        return str_repeat('d', $type === IntervalType::Perfectable ? abs($alt) : abs($alt) - 1);
    }

    /**
     * Get interval name from Pitch
     */
    private static function pitchName(Pitch $props): string
    {
        if ($props->dir === null) {
            return '';
        }

        $dir = $props->dir->value;
        $oct = $props->oct ?? 0;

        $calcNum = $props->step + 1 + 7 * $oct;
        // Edge case: descending pitch class unison
        $num = $calcNum === 0 ? $props->step + 1 : $calcNum;

        $d = $dir < 0 ? '-' : '';
        $type = self::TYPES[$props->step] === 'M' ? IntervalType::Majorable : IntervalType::Perfectable;
        $name = $d . $num . self::altToQ($type, $props->alt);

        return $name;
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
            'num' => $this->num,
            'q' => $this->q,
            'type' => $this->type !== null ? $this->type->value : '',
            'step' => $this->step,
            'alt' => $this->alt,
            'dir' => $this->dir,
            'simple' => $this->simple,
            'semitones' => $this->semitones,
            'chroma' => $this->chroma,
            'coord' => $this->coord,
            'oct' => $this->oct,
        ];
    }
}
