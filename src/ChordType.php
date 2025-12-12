<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

use Chaloman\Tonal\Data\ChordTypeData;

/**
 * Chord quality
 */
enum ChordQuality: string
{
    case Major = 'Major';
    case Minor = 'Minor';
    case Augmented = 'Augmented';
    case Diminished = 'Diminished';
    case Unknown = 'Unknown';
}

/**
 * Chord type definition and dictionary
 */
final class ChordType
{
    /**
     * Dictionary of all chord types
     * @var array<self>
     */
    private static array $dictionary = [];

    /**
     * Index for fast lookups by name, alias, chroma, and setNum
     * @var array<string|int, self>
     */
    private static array $index = [];

    /**
     * Whether the dictionary has been initialized
     */
    private static bool $initialized = false;

    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly int $setNum,
        public readonly string $chroma,
        public readonly string $normalized,
        /** @var array<string> */
        public readonly array $intervals,
        public readonly ChordQuality $quality,
        /** @var array<string> */
        public readonly array $aliases,
    ) {
    }

    /**
     * Get a chord type by name, alias, chroma, or setNum
     *
     * @param string|int $type The chord type identifier
     */
    public static function get(string|int $type): self
    {
        self::ensureInitialized();

        return self::$index[$type] ?? self::empty();
    }

    /**
     * Get all chord (long) names
     *
     * @return array<string>
     */
    public static function names(): array
    {
        self::ensureInitialized();

        return array_values(array_filter(
            array_map(fn (self $chord) => $chord->name, self::$dictionary),
        ));
    }

    /**
     * Get all chord symbols (first alias of each chord)
     *
     * @return array<string>
     */
    public static function symbols(): array
    {
        self::ensureInitialized();

        return array_values(array_filter(
            array_map(fn (self $chord) => $chord->aliases[0] ?? '', self::$dictionary),
        ));
    }

    /**
     * Get all keys used to reference chord types
     *
     * @return array<string|int>
     */
    public static function keys(): array
    {
        self::ensureInitialized();

        return array_keys(self::$index);
    }

    /**
     * Return a list of all chord types
     *
     * @return array<self>
     */
    public static function all(): array
    {
        self::ensureInitialized();

        return self::$dictionary;
    }

    /**
     * Clear the dictionary
     */
    public static function removeAll(): void
    {
        self::$dictionary = [];
        self::$index = [];
        self::$initialized = true; // Prevent re-initialization
    }

    /**
     * Add a chord to the dictionary
     *
     * @param array<string> $intervals The intervals of the chord
     * @param array<string> $aliases The chord symbols/aliases
     * @param string|null $fullName The full name of the chord
     */
    public static function add(array $intervals, array $aliases, ?string $fullName = null): void
    {
        self::ensureInitialized();

        $quality = self::getQuality($intervals);
        $pcset = Pcset::get($intervals);

        $chord = new self(
            empty: false,
            name: $fullName ?? '',
            setNum: $pcset->setNum,
            chroma: $pcset->chroma,
            normalized: $pcset->normalized,
            intervals: $intervals,
            quality: $quality,
            aliases: $aliases,
        );

        self::$dictionary[] = $chord;

        if ($chord->name !== '') {
            self::$index[$chord->name] = $chord;
        }

        self::$index[$chord->setNum] = $chord;
        self::$index[$chord->chroma] = $chord;

        foreach ($chord->aliases as $alias) {
            self::addAlias($chord, $alias);
        }
    }

    /**
     * Add an alias for a chord
     */
    public static function addAlias(self $chord, string $alias): void
    {
        self::$index[$alias] = $chord;
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
            'quality' => $this->quality->value,
            'aliases' => $this->aliases,
        ];
    }

    /**
     * Initialize the dictionary with default chords
     */
    private static function ensureInitialized(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        foreach (ChordTypeData::getData() as $data) {
            [$intervalsStr, $fullName, $aliasesStr] = $data;

            $intervals = explode(' ', $intervalsStr);
            // Split aliases preserving empty strings as valid aliases
            // Format: "M ^  maj" with double space produces ["M", "^", "", "maj"]
            // where "" is a valid alias for the chord
            $aliases = explode(' ', $aliasesStr);

            self::add($intervals, $aliases, $fullName !== '' ? $fullName : null);
        }

        // Sort by setNum
        usort(self::$dictionary, fn (self $a, self $b) => $a->setNum <=> $b->setNum);
    }

    /**
     * Determine the quality of a chord based on its intervals
     *
     * @param array<string> $intervals
     */
    private static function getQuality(array $intervals): ChordQuality
    {
        $has = fn (string $interval): bool => in_array($interval, $intervals, true);

        if ($has('5A')) {
            return ChordQuality::Augmented;
        }

        if ($has('3M')) {
            return ChordQuality::Major;
        }

        if ($has('5d')) {
            return ChordQuality::Diminished;
        }

        if ($has('3m')) {
            return ChordQuality::Minor;
        }

        return ChordQuality::Unknown;
    }

    /**
     * Create an empty chord type
     */
    private static function empty(): self
    {
        return new self(
            empty: true,
            name: '',
            setNum: 0,
            chroma: '000000000000',
            normalized: '000000000000',
            intervals: [],
            quality: ChordQuality::Unknown,
            aliases: [],
        );
    }

    /**
     * Reset initialization state (for testing)
     */
    public static function resetForTesting(): void
    {
        self::$dictionary = [];
        self::$index = [];
        self::$initialized = false;
    }
}
