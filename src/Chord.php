<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Chord representation
 */
final class ChordObject
{
    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly string $symbol,
        public readonly ?string $tonic,
        public readonly string $root,
        public readonly string $bass,
        public readonly int|float $rootDegree,
        public readonly string $type,
        public readonly int $setNum,
        public readonly string $quality,
        public readonly string $chroma,
        public readonly string $normalized,
        /** @var array<string> */
        public readonly array $aliases,
        /** @var array<string> */
        public readonly array $notes,
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
            'symbol' => $this->symbol,
            'tonic' => $this->tonic,
            'root' => $this->root,
            'bass' => $this->bass,
            'rootDegree' => $this->rootDegree,
            'type' => $this->type,
            'setNum' => $this->setNum,
            'quality' => $this->quality,
            'chroma' => $this->chroma,
            'normalized' => $this->normalized,
            'aliases' => $this->aliases,
            'notes' => $this->notes,
            'intervals' => $this->intervals,
        ];
    }
}

/**
 * Musical chords
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/chord
 */
final class Chord
{
    /**
     * Tokenize a chord name into [tonic, type, bass]
     *
     * @param string $name Chord name
     * @return array{0: string, 1: string, 2: string} [tonic, type, bass]
     *
     * @example
     * Chord::tokenize("Cmaj7") // => ["C", "maj7", ""]
     * Chord::tokenize("C7") // => ["C", "7", ""]
     * Chord::tokenize("mMaj7") // => ["", "mMaj7", ""]
     * Chord::tokenize("Cmaj7/G") // => ["C", "maj7", "G"]
     */
    public static function tokenize(string $name): array
    {
        [$letter, $acc, $oct, $type] = PitchNote::tokenizeNote($name);

        if ($letter === '') {
            return self::tokenizeBass('', $name);
        }

        if ($letter === 'A' && $type === 'ug') {
            return self::tokenizeBass('', 'aug');
        }

        // If the type contains newline or other invalid characters, treat as invalid
        if (str_contains($type, "\n") || str_contains($type, '|')) {
            return self::tokenizeBass('', $name);
        }

        return self::tokenizeBass($letter . $acc, $oct . $type);
    }

    /**
     * Tokenize bass note from chord string
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private static function tokenizeBass(string $note, string $chord): array
    {
        $split = explode('/', $chord);

        if (count($split) === 1) {
            return [$note, $split[0], ''];
        }

        [$letter, $acc, $oct, $type] = PitchNote::tokenizeNote($split[1]);

        // Only a pitch class is accepted as bass note
        if ($letter !== '' && $oct === '' && $type === '') {
            return [$note, $split[0], $letter . $acc];
        }

        return [$note, $chord, ''];
    }

    /**
     * Get a Chord from a chord name
     *
     * @param string|array{0: string, 1?: string, 2?: string} $src Chord name or [tonic, type, bass]
     *
     * @example
     * Chord::get("Cmaj7")
     * Chord::get(["C", "maj7"])
     * Chord::get(["C", "maj7", "E"])
     */
    public static function get(string|array $src): ChordObject
    {
        if (is_array($src)) {
            return self::getChord($src[1] ?? '', $src[0], $src[2] ?? null);
        }

        if ($src === '') {
            return self::empty();
        }

        [$tonic, $type, $bass] = self::tokenize($src);
        $chord = self::getChord($type, $tonic, $bass !== '' ? $bass : null);

        return $chord->empty ? self::getChord($src) : $chord;
    }

    /**
     * Alias for get()
     */
    public static function chord(string|array $src): ChordObject
    {
        return self::get($src);
    }

    /**
     * Get chord properties
     *
     * @param string $typeName The chord type name
     * @param string|null $optionalTonic Optional tonic
     * @param string|null $optionalBass Optional bass note
     */
    public static function getChord(
        string $typeName,
        ?string $optionalTonic = null,
        ?string $optionalBass = null
    ): ChordObject {
        $type = ChordType::get($typeName);
        $tonic = PitchNote::note($optionalTonic ?? '');
        $bass = PitchNote::note($optionalBass ?? '');

        if (
            $type->empty ||
            ($optionalTonic !== null && $optionalTonic !== '' && $tonic->empty) ||
            ($optionalBass !== null && $optionalBass !== '' && $bass->empty)
        ) {
            return self::empty();
        }

        $bassInterval = PitchDistance::distance($tonic->pc, $bass->pc);
        $bassIndex = array_search($bassInterval, $type->intervals, true);
        $hasRoot = $bassIndex !== false;
        $root = $hasRoot ? $bass : PitchNote::note('');
        $rootDegree = $bassIndex === false ? NAN : $bassIndex + 1;
        $hasBass = $bass->pc !== '' && $bass->pc !== $tonic->pc;

        $intervals = $type->intervals;

        if ($hasRoot) {
            for ($i = 1; $i < $rootDegree; $i++) {
                $num = $intervals[0][0];
                $quality = substr($intervals[0], 1);
                $newNum = (int) $num + 7;
                $intervals[] = $newNum . $quality;
                array_shift($intervals);
            }
        } elseif ($hasBass) {
            $ivl = Interval::subtract(PitchDistance::distance($tonic->pc, $bass->pc), '8P');
            if ($ivl !== '') {
                array_unshift($intervals, $ivl);
            }
        }

        $notes = $tonic->empty
            ? []
            : array_map(fn(string $i) => PitchDistance::transpose($tonic->pc, $i), $intervals);

        $typeNameFinal = in_array($typeName, $type->aliases, true) ? $typeName : ($type->aliases[0] ?? '');
        $symbol = ($tonic->empty ? '' : $tonic->pc) . $typeNameFinal .
            ($hasRoot && $rootDegree > 1 ? '/' . $root->pc : ($hasBass ? '/' . $bass->pc : ''));
        $name = ($optionalTonic !== null && $optionalTonic !== '' ? $tonic->pc . ' ' : '') . $type->name .
            ($hasRoot && $rootDegree > 1 ? ' over ' . $root->pc : ($hasBass ? ' over ' . $bass->pc : ''));

        return new ChordObject(
            empty: false,
            name: $name,
            symbol: $symbol,
            tonic: $tonic->pc !== '' ? $tonic->pc : null,
            root: $root->pc,
            bass: $hasBass ? $bass->pc : '',
            rootDegree: $rootDegree,
            type: $type->name,
            setNum: $type->setNum,
            quality: $type->quality->value,
            chroma: $type->chroma,
            normalized: $type->normalized,
            aliases: $type->aliases,
            notes: $notes,
            intervals: $intervals,
        );
    }

    /**
     * Transpose a chord name
     *
     * @param string $chordName The chord name
     * @param string $interval The interval to transpose by
     * @return string The transposed chord
     *
     * @example
     * Chord::transpose("Dm7", "P4") // => "Gm7"
     */
    public static function transpose(string $chordName, string $interval): string
    {
        [$tonic, $type, $bass] = self::tokenize($chordName);

        if ($tonic === '') {
            return $chordName;
        }

        $tr = PitchDistance::transpose($bass, $interval);
        $slash = $tr !== '' ? '/' . $tr : '';

        return PitchDistance::transpose($tonic, $interval) . $type . $slash;
    }

    /**
     * Get all scales where the given chord fits
     *
     * @param string $name Chord name
     * @return array<string> Scale names
     *
     * @example
     * Chord::chordScales("C7b9") // => ["phrygian dominant", "flamenco", ...]
     */
    public static function chordScales(string $name): array
    {
        $s = self::get($name);

        if ($s->empty) {
            return [];
        }

        $isChordIncluded = Pcset::isSupersetOf($s->chroma);

        return array_values(array_filter(
            array_map(
                fn(ScaleType $scale) => $isChordIncluded($scale->chroma) ? $scale->name : null,
                ScaleType::all()
            ),
            fn(?string $x) => $x !== null
        ));
    }

    /**
     * Get all chord names that are a superset of the given one
     *
     * @param string $chordName Chord name
     * @return array<string> Chord symbols
     *
     * @example
     * Chord::extended("CMaj7") // => ["Cmaj#4", "Cmaj7#9#11", ...]
     */
    public static function extended(string $chordName): array
    {
        $s = self::get($chordName);

        if ($s->empty) {
            return [];
        }

        $isSuperset = Pcset::isSupersetOf($s->chroma);

        return array_values(array_filter(
            array_map(
                fn(ChordType $chord) => $isSuperset($chord->chroma) ? $s->tonic . ($chord->aliases[0] ?? '') : null,
                ChordType::all()
            ),
            fn(?string $x) => $x !== null
        ));
    }

    /**
     * Get all chord names that are a subset of the given one
     *
     * @param string $chordName Chord name
     * @return array<string> Chord symbols
     *
     * @example
     * Chord::reduced("CMaj7") // => ["C5", "CM"]
     */
    public static function reduced(string $chordName): array
    {
        $s = self::get($chordName);

        if ($s->empty) {
            return [];
        }

        $isSubset = Pcset::isSubsetOf($s->chroma);

        return array_values(array_filter(
            array_map(
                fn(ChordType $chord) => $isSubset($chord->chroma) ? $s->tonic . ($chord->aliases[0] ?? '') : null,
                ChordType::all()
            ),
            fn(?string $x) => $x !== null
        ));
    }

    /**
     * Return the chord notes
     *
     * @param string|array $chordName Chord name or tokens
     * @param string|null $tonic Optional tonic override
     * @return array<string> Notes
     */
    public static function notes(string|array $chordName, ?string $tonic = null): array
    {
        $chord = self::get($chordName);
        $note = $tonic ?? $chord->tonic;

        if ($note === null || $note === '' || $chord->empty) {
            return [];
        }

        return array_map(fn(string $ivl) => PitchDistance::transpose($note, $ivl), $chord->intervals);
    }

    /**
     * Returns a function to get a note name from the chord degree (1-based)
     *
     * @param string|array $chordName Chord name
     * @param string|null $tonic Optional tonic override
     * @return callable(int): string
     *
     * @example
     * array_map(Chord::degrees("C"), [1, 2, 3, 4]) // => ["C", "E", "G", "C"]
     */
    public static function degrees(string|array $chordName, ?string $tonic = null): callable
    {
        $chord = self::get($chordName);
        $note = $tonic ?? $chord->tonic;
        $transpose = PitchDistance::tonicIntervalsTransposer($chord->intervals, $note);

        return fn(int $degree): string =>
            $degree !== 0 ? $transpose($degree > 0 ? $degree - 1 : $degree) : '';
    }

    /**
     * Returns a function to get a note name from the chord step (0-based)
     *
     * @param string|array $chordName Chord name
     * @param string|null $tonic Optional tonic override
     * @return callable(int): string
     *
     * @example
     * array_map(Chord::steps("aug", "C4"), [-3, -2, -1, 0, 1, 2, 3])
     * // => ["C3", "E3", "G#3", "C4", "E4", "G#4", "C5"]
     */
    public static function steps(string|array $chordName, ?string $tonic = null): callable
    {
        $chord = self::get($chordName);
        $note = $tonic ?? $chord->tonic;

        return PitchDistance::tonicIntervalsTransposer($chord->intervals, $note);
    }

    /**
     * Detect chord from notes
     *
     * @param array<string> $notes Notes to detect
     * @return array<string> Detected chord names
     */
    public static function detect(array $notes): array
    {
        return ChordDetect::detect($notes);
    }

    /**
     * Create empty chord
     */
    private static function empty(): ChordObject
    {
        return new ChordObject(
            empty: true,
            name: '',
            symbol: '',
            tonic: null,
            root: '',
            bass: '',
            rootDegree: NAN,
            type: '',
            setNum: 0,
            quality: 'Unknown',
            chroma: '',
            normalized: '',
            aliases: [],
            notes: [],
            intervals: [],
        );
    }
}
