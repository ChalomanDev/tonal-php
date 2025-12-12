<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

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
    ) {}

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

/**
 * Greek modes (church modes)
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/mode
 */
final class Mode
{
    /**
     * Mode definitions: [modeNum, setNum, alt, name, triad, seventh, alias?]
     */
    private const array MODES = [
        [0, 2773, 0, 'ionian', '', 'Maj7', 'major'],
        [1, 2902, 2, 'dorian', 'm', 'm7', null],
        [2, 3418, 4, 'phrygian', 'm', 'm7', null],
        [3, 2741, -1, 'lydian', '', 'Maj7', null],
        [4, 2774, 1, 'mixolydian', '', '7', null],
        [5, 2906, 3, 'aeolian', 'm', 'm7', 'minor'],
        [6, 3434, 5, 'locrian', 'dim', 'm7b5', null],
    ];

    /**
     * @var array<ModeType>
     */
    private static array $modes = [];

    /**
     * @var array<string, ModeType>
     */
    private static array $index = [];

    /**
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * Get a mode by name
     *
     * @param string|ModeType|array{name: string} $name Mode name, ModeType object, or object with name property
     *
     * @example
     * Mode::get('dorian')
     * Mode::get('major')
     * Mode::get(Mode::get('major'))
     */
    public static function get(string|ModeType|array $name): ModeType
    {
        self::ensureInitialized();

        if ($name instanceof ModeType) {
            return $name;
        }

        if (is_array($name) && isset($name['name'])) {
            return self::get($name['name']);
        }

        if (is_string($name)) {
            $key = strtolower($name);

            return self::$index[$key] ?? self::empty();
        }

        return self::empty();
    }

    /**
     * Get all modes
     *
     * @return array<ModeType>
     */
    public static function all(): array
    {
        self::ensureInitialized();

        return self::$modes;
    }

    /**
     * Get all mode names
     *
     * @return array<string>
     */
    public static function names(): array
    {
        self::ensureInitialized();

        return array_map(fn(ModeType $mode) => $mode->name, self::$modes);
    }

    /**
     * Get notes of a mode with a given tonic
     *
     * @param string|ModeType|array{name: string} $modeName Mode name
     * @param string $tonic Tonic note
     * @return array<string>
     *
     * @example
     * Mode::notes('major', 'C') // => ['C', 'D', 'E', 'F', 'G', 'A', 'B']
     * Mode::notes('dorian', 'C') // => ['C', 'D', 'Eb', 'F', 'G', 'A', 'Bb']
     */
    public static function notes(string|ModeType|array $modeName, string $tonic): array
    {
        $mode = self::get($modeName);

        if ($mode->empty) {
            return [];
        }

        return array_map(
            fn(string $ivl) => PitchDistance::transpose($tonic, $ivl),
            $mode->intervals
        );
    }

    /**
     * Get triads of a mode with a given tonic
     *
     * @param string|ModeType|array{name: string} $modeName Mode name
     * @param string $tonic Tonic note
     * @return array<string>
     *
     * @example
     * Mode::triads('minor', 'C') // => ['Cm', 'Ddim', 'Eb', 'Fm', 'Gm', 'Ab', 'Bb']
     */
    public static function triads(string|ModeType|array $modeName, string $tonic): array
    {
        return self::chords(
            array_map(fn(array $m) => $m[4], self::MODES),
            $modeName,
            $tonic
        );
    }

    /**
     * Get seventh chords of a mode with a given tonic
     *
     * @param string|ModeType|array{name: string} $modeName Mode name
     * @param string $tonic Tonic note
     * @return array<string>
     *
     * @example
     * Mode::seventhChords('major', 'C#') // => ['C#Maj7', 'D#m7', ...]
     */
    public static function seventhChords(string|ModeType|array $modeName, string $tonic): array
    {
        return self::chords(
            array_map(fn(array $m) => $m[5], self::MODES),
            $modeName,
            $tonic
        );
    }

    /**
     * Get the distance (interval) between two modes
     *
     * @param string|ModeType|array{name: string} $destination Destination mode
     * @param string|ModeType|array{name: string} $source Source mode
     * @return string Interval between the modes
     *
     * @example
     * Mode::distance('major', 'minor') // => "3m"
     */
    public static function distance(string|ModeType|array $destination, string|ModeType|array $source): string
    {
        $from = self::get($source);
        $to = self::get($destination);

        if ($from->empty || $to->empty) {
            return '';
        }

        return Interval::simplify(Interval::transposeFifths('1P', $to->alt - $from->alt));
    }

    /**
     * Get the relative tonic when changing from one mode to another
     *
     * @param string|ModeType|array{name: string} $destination Destination mode
     * @param string|ModeType|array{name: string} $source Source mode
     * @param string $tonic Original tonic
     * @return string New tonic for the destination mode
     *
     * @example
     * Mode::relativeTonic('major', 'minor', 'A') // => 'C'
     */
    public static function relativeTonic(
        string|ModeType|array $destination,
        string|ModeType|array $source,
        string $tonic
    ): string {
        return PitchDistance::transpose($tonic, self::distance($destination, $source));
    }

    /**
     * Get chords for a mode
     *
     * @param array<string> $chordTypes Chord types for each degree
     * @param string|ModeType|array{name: string} $modeName Mode name
     * @param string $tonic Tonic note
     * @return array<string>
     */
    private static function chords(array $chordTypes, string|ModeType|array $modeName, string $tonic): array
    {
        $mode = self::get($modeName);

        if ($mode->empty) {
            return [];
        }

        $rotatedChords = Collection::rotate($mode->modeNum, $chordTypes);
        $tonics = array_map(
            fn(string $i) => PitchDistance::transpose($tonic, $i),
            $mode->intervals
        );

        $result = [];
        foreach ($rotatedChords as $i => $chord) {
            $result[] = $tonics[$i] . $chord;
        }

        return $result;
    }

    /**
     * Initialize modes dictionary
     */
    private static function ensureInitialized(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        foreach (self::MODES as $modeData) {
            $mode = self::toMode($modeData);
            self::$modes[] = $mode;
            self::$index[$mode->name] = $mode;

            foreach ($mode->aliases as $alias) {
                self::$index[$alias] = $mode;
            }
        }
    }

    /**
     * Convert mode data to ModeType
     *
     * @param array{int, int, int, string, string, string, ?string} $data
     */
    private static function toMode(array $data): ModeType
    {
        [$modeNum, $setNum, $alt, $name, $triad, $seventh, $alias] = $data;

        $aliases = $alias !== null ? [$alias] : [];
        $chroma = str_pad(decbin($setNum), 12, '0', STR_PAD_LEFT);

        // Get intervals from ScaleType
        $scaleType = ScaleType::get($name);
        $intervals = $scaleType->intervals;

        return new ModeType(
            empty: false,
            name: $name,
            modeNum: $modeNum,
            setNum: $setNum,
            chroma: $chroma,
            normalized: $chroma,
            alt: $alt,
            triad: $triad,
            seventh: $seventh,
            aliases: $aliases,
            intervals: $intervals,
        );
    }

    /**
     * Create empty mode
     */
    private static function empty(): ModeType
    {
        return new ModeType(
            empty: true,
            name: '',
            modeNum: 0,
            setNum: 0,
            chroma: '000000000000',
            normalized: '000000000000',
            alt: 0,
            triad: '',
            seventh: '',
            aliases: [],
            intervals: [],
        );
    }

    /**
     * Reset for testing
     */
    public static function resetForTesting(): void
    {
        self::$modes = [];
        self::$index = [];
        self::$initialized = false;
    }
}
