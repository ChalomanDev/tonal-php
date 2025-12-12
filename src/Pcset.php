<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Pitch Class Set (Pcset) representation and operations
 *
 * A pitch class set is a collection of pitch classes represented as:
 * - A 12-character chroma string (e.g., "101010000000")
 * - A set number between 1 and 4095
 * - An array of note or interval names
 */
final class Pcset
{
    /**
     * Empty chroma string
     */
    private const string EMPTY_CHROMA = '000000000000';

    /**
     * Intervals for each chroma position (starting from C)
     */
    private const array IVLS = [
        '1P', '2m', '2M', '3m', '3M', '4P', '5d', '5P', '6m', '6M', '7m', '7M',
    ];

    /**
     * Regex to validate a chroma string
     */
    private const string CHROMA_REGEX = '/^[01]{12}$/';

    /**
     * Cache for parsed pcsets
     * @var array<string, self>
     */
    private static array $cache = [];

    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly int $setNum,
        public readonly string $chroma,
        public readonly string $normalized,
        /** @var array<string> */
        public readonly array $intervals,
    ) {
    }

    /**
     * Get the pitch class set from various sources:
     * - A chroma string (12-char binary string)
     * - A set number (1-4095)
     * - An array of note or interval names
     * - A Pcset object
     *
     * @param string|int|array<string>|self $src The source
     * @return self The pitch class set
     */
    public static function get(string|int|array|self $src): self
    {
        if ($src instanceof self) {
            return $src;
        }

        $chroma = self::toChroma($src);

        if (isset(self::$cache[$chroma])) {
            return self::$cache[$chroma];
        }

        $pcset = self::chromaToPcset($chroma);
        self::$cache[$chroma] = $pcset;

        return $pcset;
    }

    /**
     * Get pitch class set chroma
     *
     * @param string|int|array<string>|self $set
     * @return string The chroma string
     *
     * @example Pcset::chroma(['c', 'd', 'e']) // => "101010000000"
     */
    public static function chroma(string|int|array|self $set): string
    {
        return self::get($set)->chroma;
    }

    /**
     * Get intervals (from C) of a set
     *
     * @param string|int|array<string>|self $set
     * @return array<string>
     */
    public static function intervals(string|int|array|self $set): array
    {
        return self::get($set)->intervals;
    }

    /**
     * Get pitch class set number
     *
     * @param string|int|array<string>|self $set
     * @return int The set number (0-4095)
     *
     * @example Pcset::num(['c', 'd', 'e']) // => 2688
     */
    public static function num(string|int|array|self $set): int
    {
        return self::get($set)->setNum;
    }

    /**
     * Check if a string is a valid chroma
     */
    public static function isChroma(mixed $set): bool
    {
        return is_string($set) && preg_match(self::CHROMA_REGEX, $set) === 1;
    }

    /**
     * Get notes from a pitch class set (transposed from C)
     *
     * @param string|int|array<string>|self $set
     * @return array<string>
     */
    public static function notes(string|int|array|self $set): array
    {
        $pcset = self::get($set);

        return array_map(
            fn (string $ivl) => PitchDistance::transpose('C', $ivl),
            $pcset->intervals,
        );
    }

    /**
     * Get a list of all possible pitch class sets (all possible chromas)
     * having C as root. There are 2048 different chromas.
     *
     * @return array<string> Array of chroma strings from '100000000000' to '111111111111'
     */
    public static function chromas(): array
    {
        return array_map(
            fn (int $n) => self::setNumToChroma($n),
            Collection::range(2048, 4095),
        );
    }

    /**
     * Get the rotations (modes) of a pitch class set
     *
     * @param string|int|array<string>|self $set The set
     * @param bool $normalize If true (default), remove rotations starting with "0"
     * @return array<string> Array of chroma strings representing all modes
     */
    public static function modes(string|int|array|self $set, bool $normalize = true): array
    {
        $pcs = self::get($set);

        if ($pcs->empty) {
            return [];
        }

        $binary = str_split($pcs->chroma);
        $result = [];

        for ($i = 0; $i < 12; $i++) {
            $r = Collection::rotate($i, $binary);
            if (!$normalize || $r[0] === '1') {
                $result[] = implode('', $r);
            }
        }

        return Collection::compact($result);
    }

    /**
     * Test if two pitch class sets are equal
     *
     * @param string|int|array<string>|self $s1
     * @param string|int|array<string>|self $s2
     */
    public static function isEqual(string|int|array|self $s1, string|int|array|self $s2): bool
    {
        return self::get($s1)->setNum === self::get($s2)->setNum;
    }

    /**
     * Create a function that tests if a collection of notes is a subset of a given set
     *
     * @param string|int|array<string>|self $set The superset to test against
     * @return callable(string|int|array<string>|self): bool
     *
     * @example
     * $inCMajor = Pcset::isSubsetOf(['C', 'E', 'G']);
     * $inCMajor(['e6', 'c4']) // => true
     * $inCMajor(['e6', 'c4', 'd3']) // => false
     */
    public static function isSubsetOf(string|int|array|self $set): callable
    {
        $s = self::get($set)->setNum;

        return function (string|int|array|self $notes) use ($s): bool {
            $o = self::get($notes)->setNum;
            // o is a subset of s if: o & s === o (all bits of o are in s)
            // and o !== s (proper subset, not equal)
            return $s !== 0 && $s !== $o && ($o & $s) === $o;
        };
    }

    /**
     * Create a function that tests if a collection of notes is a superset of a given set
     *
     * @param string|int|array<string>|self $set The subset to test against
     * @return callable(string|int|array<string>|self): bool
     *
     * @example
     * $extendsCMajor = Pcset::isSupersetOf(['C', 'E', 'G']);
     * $extendsCMajor(['e6', 'a', 'c4', 'g2']) // => true
     * $extendsCMajor(['c6', 'e4', 'g3']) // => false
     */
    public static function isSupersetOf(string|int|array|self $set): callable
    {
        $s = self::get($set)->setNum;

        return function (string|int|array|self $notes) use ($s): bool {
            $o = self::get($notes)->setNum;
            // o is a superset of s if: o | s === o (o contains all bits of s)
            // and o !== s (proper superset, not equal)
            return $s !== 0 && $s !== $o && ($o | $s) === $o;
        };
    }

    /**
     * Create a function that tests if a note is included in the set
     *
     * @param string|int|array<string>|self $set The set to test against
     * @return callable(string): bool
     *
     * @example
     * $isNoteInCMajor = Pcset::isNoteIncludedIn(['C', 'E', 'G']);
     * $isNoteInCMajor('C4') // => true
     * $isNoteInCMajor('C#4') // => false
     */
    public static function isNoteIncludedIn(string|int|array|self $set): callable
    {
        $s = self::get($set);

        return function (string $noteName) use ($s): bool {
            $n = PitchNote::note($noteName);

            return !$s->empty && !$n->empty && $s->chroma[$n->chroma] === '1';
        };
    }

    /**
     * Create a filter function for notes based on a pitch class set
     *
     * @param string|int|array<string>|self $set The set to filter by
     * @return callable(array<string>): array<string>
     *
     * @example
     * $inCMajor = Pcset::filter(['C', 'D', 'E']);
     * $inCMajor(['c2', 'c#2', 'd2', 'c3', 'c#3', 'd3']) // => ['c2', 'd2', 'c3', 'd3']
     */
    public static function filter(string|int|array|self $set): callable
    {
        $isIncluded = self::isNoteIncludedIn($set);

        return fn (array $notes): array => array_values(array_filter($notes, $isIncluded));
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
            'setNum' => $this->setNum,
            'chroma' => $this->chroma,
            'normalized' => $this->normalized,
            'intervals' => $this->intervals,
        ];
    }

    /**
     * Convert source to chroma string
     *
     * @param string|int|array<string> $src
     */
    private static function toChroma(string|int|array $src): string
    {
        if (self::isChroma($src)) {
            return $src;
        }

        if (self::isPcsetNum($src)) {
            return self::setNumToChroma($src);
        }

        if (is_array($src)) {
            return self::listToChroma($src);
        }

        return self::EMPTY_CHROMA;
    }

    /**
     * Check if value is a valid pcset number
     */
    private static function isPcsetNum(mixed $set): bool
    {
        return is_int($set) && $set >= 0 && $set <= 4095;
    }

    /**
     * Convert a set number to chroma string
     */
    private static function setNumToChroma(int $num): string
    {
        return str_pad(decbin($num), 12, '0', STR_PAD_LEFT);
    }

    /**
     * Convert chroma string to number
     */
    private static function chromaToNumber(string $chroma): int
    {
        return (int) bindec($chroma);
    }

    /**
     * Get intervals from chroma string
     *
     * @return array<string>
     */
    private static function chromaToIntervals(string $chroma): array
    {
        $intervals = [];

        for ($i = 0; $i < 12; $i++) {
            if ($chroma[$i] === '1') {
                $intervals[] = self::IVLS[$i];
            }
        }

        return $intervals;
    }

    /**
     * Convert a list of notes/intervals to chroma
     *
     * @param array<string> $set
     */
    private static function listToChroma(array $set): string
    {
        if (empty($set)) {
            return self::EMPTY_CHROMA;
        }

        $binary = array_fill(0, 12, 0);

        foreach ($set as $item) {
            // Try as note first
            $pitch = PitchNote::note($item);

            // If not a note, try as interval
            if ($pitch->empty) {
                $pitch = PitchInterval::interval($item);
            }

            if (!$pitch->empty) {
                $binary[$pitch->chroma] = 1;
            }
        }

        return implode('', $binary);
    }

    /**
     * Get all rotations of a chroma string
     *
     * @return array<string>
     */
    private static function chromaRotations(string $chroma): array
    {
        $binary = str_split($chroma);
        $result = [];

        for ($i = 0; $i < 12; $i++) {
            $result[] = implode('', Collection::rotate($i, $binary));
        }

        return $result;
    }

    /**
     * Build a Pcset from a chroma string
     */
    private static function chromaToPcset(string $chroma): self
    {
        $setNum = self::chromaToNumber($chroma);

        if ($setNum === 0) {
            return self::empty();
        }

        // Find the normalized form (rotation with highest binary value >= 2048)
        $rotations = self::chromaRotations($chroma);
        $validRotations = array_filter(
            array_map([self::class, 'chromaToNumber'], $rotations),
            fn (int $n) => $n >= 2048,
        );

        sort($validRotations);
        $normalizedNum = $validRotations[0] ?? $setNum;
        $normalized = self::setNumToChroma($normalizedNum);

        $intervals = self::chromaToIntervals($chroma);

        return new self(
            empty: false,
            name: '',
            setNum: $setNum,
            chroma: $chroma,
            normalized: $normalized,
            intervals: $intervals,
        );
    }

    /**
     * Create empty pcset
     */
    private static function empty(): self
    {
        return new self(
            empty: true,
            name: '',
            setNum: 0,
            chroma: self::EMPTY_CHROMA,
            normalized: self::EMPTY_CHROMA,
            intervals: [],
        );
    }
}
