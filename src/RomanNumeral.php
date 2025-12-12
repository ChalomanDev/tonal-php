<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Roman numeral representation for chord progressions
 */
final class RomanNumeral implements PitchLike
{
    /**
     * Major roman numerals
     */
    private const array NAMES = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'];

    /**
     * Minor roman numerals
     */
    private const array NAMES_MINOR = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii'];

    /**
     * Regex for parsing roman numerals
     */
    private const string REGEX = '/^(#{1,}|b{1,}|x{1,}|)(IV|I{1,3}|VI{0,2}|iv|i{1,3}|vi{0,2})([^IViv]*)$/';

    /**
     * Cache for parsed roman numerals
     * @var array<string, self>
     */
    private static array $cache = [];

    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly string $roman,
        public readonly string $interval,
        public readonly string $acc,
        public readonly string $chordType,
        public readonly bool $major,
        public readonly int $step,
        public readonly int $alt,
        public readonly int $oct,
        public readonly int $dir,
    ) {}

    /**
     * Get properties of a roman numeral string
     *
     * @param string|int|Pitch|PitchInterval $src Roman numeral string, step number, or Pitch
     * @return self The roman numeral properties
     *
     * @example
     * RomanNumeral::get("VIIb5") // => RomanNumeral with name: "VII", chordType: "b5"
     */
    public static function get(string|int|Pitch|PitchInterval $src): self
    {
        if (is_string($src)) {
            if (isset(self::$cache[$src])) {
                return self::$cache[$src];
            }

            $romanNumeral = self::parse($src);
            self::$cache[$src] = $romanNumeral;

            return $romanNumeral;
        }

        if (is_int($src)) {
            $name = self::NAMES[$src] ?? '';

            return self::get($name);
        }

        if ($src instanceof Pitch) {
            return self::fromPitch($src);
        }

        if ($src instanceof PitchInterval) {
            // Create a Pitch from interval properties
            $dir = $src->dir === 1 ? Direction::Ascending : Direction::Descending;
            $pitch = new Pitch($src->step, $src->alt, $src->oct, $dir);

            return self::fromPitch($pitch);
        }

        return self::empty();
    }

    /**
     * Get roman numeral names
     *
     * @param bool $major Whether to return major (uppercase) names
     * @return array<string> The roman numeral names
     */
    public static function names(bool $major = true): array
    {
        return $major ? self::NAMES : self::NAMES_MINOR;
    }

    /**
     * Tokenize a roman numeral string
     *
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    public static function tokenize(string $str): array
    {
        if (!preg_match(self::REGEX, $str, $m)) {
            return ['', '', '', ''];
        }

        return [$m[0], $m[1], $m[2], $m[3]];
    }

    /**
     * Create from a Pitch object
     */
    private static function fromPitch(Pitch $pitch): self
    {
        $acc = PitchNote::altToAcc($pitch->alt);
        $name = $acc . (self::NAMES[$pitch->step] ?? '');

        return self::get($name);
    }

    /**
     * Parse a roman numeral string
     */
    private static function parse(string $src): self
    {
        [$name, $acc, $roman, $chordType] = self::tokenize($src);

        if ($roman === '') {
            return self::empty();
        }

        $upperRoman = strtoupper($roman);
        $step = array_search($upperRoman, self::NAMES, true);

        if ($step === false) {
            return self::empty();
        }

        $alt = PitchNote::accToAlt($acc);
        $dir = 1;

        // Get interval name using PitchInterval
        $pitch = new Pitch($step, $alt, 0, Direction::Ascending);
        $intervalName = self::pitchToIntervalName($pitch);

        return new self(
            empty: false,
            name: $name,
            roman: $roman,
            interval: $intervalName,
            acc: $acc,
            chordType: $chordType,
            major: $roman === $upperRoman,
            step: $step,
            alt: $alt,
            oct: 0,
            dir: $dir,
        );
    }

    /**
     * Convert pitch to interval name
     */
    private static function pitchToIntervalName(Pitch $pitch): string
    {
        $coord = Pitch::coordinates($pitch);

        return PitchInterval::coordToInterval($coord)->name;
    }

    /**
     * Create empty roman numeral
     */
    private static function empty(): self
    {
        return new self(
            empty: true,
            name: '',
            roman: '',
            interval: '',
            acc: '',
            chordType: '',
            major: false,
            step: 0,
            alt: 0,
            oct: 0,
            dir: 0,
        );
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
            'roman' => $this->roman,
            'interval' => $this->interval,
            'acc' => $this->acc,
            'chordType' => $this->chordType,
            'major' => $this->major,
            'step' => $this->step,
            'alt' => $this->alt,
            'oct' => $this->oct,
            'dir' => $this->dir,
        ];
    }

    // PitchLike interface implementation

    public function getStep(): int
    {
        return $this->step;
    }

    public function getAlt(): int
    {
        return $this->alt;
    }

    public function getOct(): int
    {
        return $this->oct;
    }

    public function getDir(): int
    {
        return $this->dir;
    }
}
