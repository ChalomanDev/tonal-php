<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

use Chaloman\Tonal\Data\ScaleTypeData;

/**
 * Scale type definition and dictionary
 */
final class ScaleType
{
    /**
     * Dictionary of all scale types
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
        /** @var array<string> */
        public readonly array $aliases,
    ) {}

    /**
     * Get a scale type by name, alias, chroma, or setNum
     *
     * @param string|int $type The scale type identifier
     */
    public static function get(string|int $type): self
    {
        self::ensureInitialized();

        return self::$index[$type] ?? self::empty();
    }

    /**
     * Get all scale names
     *
     * @return array<string>
     */
    public static function names(): array
    {
        self::ensureInitialized();

        return array_map(fn(self $scale) => $scale->name, self::$dictionary);
    }

    /**
     * Get all keys used to reference scale types
     *
     * @return array<string|int>
     */
    public static function keys(): array
    {
        self::ensureInitialized();

        return array_keys(self::$index);
    }

    /**
     * Return a list of all scale types
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
     * Add a scale to the dictionary
     *
     * @param array<string> $intervals The intervals of the scale
     * @param string $name The name of the scale
     * @param array<string> $aliases Alternative names for the scale
     * @return self The created scale type
     */
    public static function add(array $intervals, string $name, array $aliases = []): self
    {
        self::ensureInitialized();

        $pcset = Pcset::get($intervals);

        $scale = new self(
            empty: false,
            name: $name,
            setNum: $pcset->setNum,
            chroma: $pcset->chroma,
            normalized: $pcset->normalized,
            intervals: $intervals,
            aliases: $aliases,
        );

        self::$dictionary[] = $scale;

        self::$index[$scale->name] = $scale;
        self::$index[$scale->setNum] = $scale;
        self::$index[$scale->chroma] = $scale;

        foreach ($scale->aliases as $alias) {
            self::addAlias($scale, $alias);
        }

        return $scale;
    }

    /**
     * Add an alias for a scale
     */
    public static function addAlias(self $scale, string $alias): void
    {
        self::$index[$alias] = $scale;
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
            'aliases' => $this->aliases,
        ];
    }

    /**
     * Initialize the dictionary with default scales
     */
    private static function ensureInitialized(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        foreach (ScaleTypeData::getData() as $data) {
            $intervalsStr = $data[0];
            $name = $data[1];
            $aliases = array_slice($data, 2);

            $intervals = explode(' ', $intervalsStr);

            self::add($intervals, $name, $aliases);
        }
    }

    /**
     * Create an empty scale type
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
