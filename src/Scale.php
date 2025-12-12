<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

use Chaloman\Tonal\ValueObjects\ScaleObject;

/**
 * Musical scales
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/scale
 */
final class Scale
{
    /**
     * Tokenize a scale name into [tonic, type]
     *
     * @param string $name Scale name
     * @return array{0: string, 1: string} [tonic, type]
     *
     * @example
     * Scale::tokenize("C mixolydian") // => ["C", "mixolydian"]
     * Scale::tokenize("anything is valid") // => ["", "anything is valid"]
     */
    public static function tokenize(string $name): array
    {
        if ($name === '') {
            return ['', ''];
        }

        $i = strpos($name, ' ');

        if ($i === false) {
            // No space - check if it's a note
            $n = PitchNote::note($name);

            return $n->empty ? ['', strtolower($name)] : [$n->name, ''];
        }

        $tonic = PitchNote::note(substr($name, 0, $i));

        if ($tonic->empty) {
            $n = PitchNote::note($name);

            return $n->empty ? ['', strtolower($name)] : [$n->name, ''];
        }

        $type = substr($name, strlen($tonic->name) + 1);
        $type = $type !== '' ? strtolower($type) : '';

        return [$tonic->name, $type];
    }

    /**
     * Get all scale names
     *
     * @return array<string>
     */
    public static function names(): array
    {
        return ScaleType::names();
    }

    /**
     * Get a scale from a scale name
     *
     * @param string|array{0: string, 1: string} $src Scale name or [tonic, type]
     *
     * @example
     * Scale::get("C major")
     * Scale::get(["C", "major"])
     * Scale::get("pentatonic")
     */
    public static function get(string|array $src): ScaleObject
    {
        $tokens = is_array($src) ? $src : self::tokenize($src);
        $tonic = PitchNote::note($tokens[0]);
        $st = ScaleType::get($tokens[1]);

        if ($st->empty) {
            return self::empty();
        }

        $tonicName = $tonic->empty ? '' : $tonic->name;
        $type = $st->name;

        $notes = $tonicName !== ''
            ? array_map(fn (string $i) => PitchDistance::transpose($tonicName, $i), $st->intervals)
            : [];

        $name = $tonicName !== '' ? $tonicName . ' ' . $type : $type;

        return new ScaleObject(
            empty: false,
            name: $name,
            type: $type,
            tonic: $tonicName !== '' ? $tonicName : null,
            setNum: $st->setNum,
            chroma: $st->chroma,
            normalized: $st->normalized,
            aliases: $st->aliases,
            notes: $notes,
            intervals: $st->intervals,
        );
    }

    /**
     * Detect scale from notes
     *
     * @param array<string> $notes Notes to detect scale from
     * @param array{tonic?: string, match?: string} $options Detection options
     * @return array<string> Detected scale names
     *
     * @example
     * Scale::detect(['C', 'D', 'E', 'F', 'G', 'A', 'B'], ['match' => 'exact']) // => ['C major']
     */
    public static function detect(array $notes, array $options = []): array
    {
        $notesChroma = Pcset::chroma($notes);
        $tonicNote = PitchNote::note($options['tonic'] ?? $notes[0] ?? '');
        $tonicChroma = $tonicNote->empty ? null : $tonicNote->chroma;

        if ($tonicChroma === null) {
            return [];
        }

        $pitchClasses = str_split($notesChroma);
        $pitchClasses[$tonicChroma] = '1';
        $scaleChroma = implode('', Collection::rotate($tonicChroma, $pitchClasses));

        // Find exact match
        $match = null;
        foreach (ScaleType::all() as $scaleType) {
            if ($scaleType->chroma === $scaleChroma) {
                $match = $scaleType;
                break;
            }
        }

        $results = [];
        if ($match !== null) {
            $results[] = $tonicNote->name . ' ' . $match->name;
        }

        if (($options['match'] ?? '') === 'exact') {
            return $results;
        }

        // Add extended scales
        foreach (self::extended($scaleChroma) as $scaleName) {
            $results[] = $tonicNote->name . ' ' . $scaleName;
        }

        return $results;
    }

    /**
     * Get all chords that fit into a scale
     *
     * @param string $name Scale name
     * @return array<string> Chord symbols
     *
     * @example
     * Scale::scaleChords("pentatonic") // => ["5", "M", "6", "sus2", "Madd9"]
     */
    public static function scaleChords(string $name): array
    {
        $s = self::get($name);

        if ($s->empty) {
            return [];
        }

        $inScale = Pcset::isSubsetOf($s->chroma);

        return array_values(array_filter(
            array_map(
                fn (ChordType $chord) => $inScale($chord->chroma) ? ($chord->aliases[0] ?? '') : null,
                ChordType::all(),
            ),
            fn (?string $x) => $x !== null && $x !== '',
        ));
    }

    /**
     * Get all scales that are supersets of the given one
     *
     * @param string $name Scale name or chroma
     * @return array<string> Scale names
     *
     * @example
     * Scale::extended("major") // => ["bebop", "bebop dominant", ...]
     */
    public static function extended(string $name): array
    {
        $chroma = Pcset::isChroma($name) ? $name : self::get($name)->chroma;

        if ($chroma === '' || $chroma === '000000000000') {
            return [];
        }

        $isSuperset = Pcset::isSupersetOf($chroma);

        return array_values(array_filter(
            array_map(
                fn (ScaleType $scale) => $isSuperset($scale->chroma) ? $scale->name : null,
                ScaleType::all(),
            ),
            fn (?string $x) => $x !== null,
        ));
    }

    /**
     * Get all scales that are subsets of the given one
     *
     * @param string $name Scale name
     * @return array<string> Scale names
     *
     * @example
     * Scale::reduced("major") // => ["ionian pentatonic", "major pentatonic", "ritusen"]
     */
    public static function reduced(string $name): array
    {
        $s = self::get($name);

        if ($s->empty) {
            return [];
        }

        $isSubset = Pcset::isSubsetOf($s->chroma);

        return array_values(array_filter(
            array_map(
                fn (ScaleType $scale) => $isSubset($scale->chroma) ? $scale->name : null,
                ScaleType::all(),
            ),
            fn (?string $x) => $x !== null,
        ));
    }

    /**
     * Get notes from an array of note names as a pitch class set
     *
     * @param array<string> $notes Notes
     * @return array<string> Pitch classes with same tonic
     *
     * @example
     * Scale::scaleNotes(['C4', 'c3', 'C5']) // => ["C"]
     * Scale::scaleNotes(['D4', 'c#5', 'A5', 'F#6']) // => ["D", "F#", "A", "C#"]
     */
    public static function scaleNotes(array $notes): array
    {
        $pcset = array_values(array_filter(
            array_map(fn (string $n) => PitchNote::note($n)->pc, $notes),
        ));

        if (empty($pcset)) {
            return [];
        }

        $tonic = $pcset[0];
        $scale = Note::sortedUniqNames($pcset);
        $tonicIndex = array_search($tonic, $scale, true);

        if ($tonicIndex === false) {
            return $scale;
        }

        return Collection::rotate($tonicIndex, $scale);
    }

    /**
     * Get mode names of a scale
     *
     * @param string $name Scale name
     * @return array<array{0: string, 1: string}> Array of [tonic/interval, mode name]
     *
     * @example
     * Scale::modeNames("C pentatonic") // => [["C", "major pentatonic"], ["D", "egyptian"], ...]
     */
    public static function modeNames(string $name): array
    {
        $s = self::get($name);

        if ($s->empty) {
            return [];
        }

        $tonics = $s->tonic !== null ? $s->notes : $s->intervals;
        $modes = Pcset::modes($s->chroma);

        $result = [];
        foreach ($modes as $i => $modeChroma) {
            $modeScale = self::get($modeChroma);
            if ($modeScale->name !== '' && isset($tonics[$i])) {
                $result[] = [$tonics[$i], $modeScale->name];
            }
        }

        return $result;
    }

    /**
     * Create a function that returns notes within a scale range
     *
     * @param string|array<string> $scale Scale name or array of notes
     * @return callable(string, string): array<string>
     *
     * @example
     * $range = Scale::rangeOf("C pentatonic");
     * $range("C4", "C5") // => ["C4", "D4", "E4", "G4", "A4", "C5"]
     */
    public static function rangeOf(string|array $scale): callable
    {
        $getName = self::getNoteNameOf($scale);

        return function (string $fromNote, string $toNote) use ($getName): array {
            $from = PitchNote::note($fromNote)->height;
            $to = PitchNote::note($toNote)->height;

            if ($from === 0 || $to === 0) {
                // Check if note was actually parsed
                $fromN = PitchNote::note($fromNote);
                $toN = PitchNote::note($toNote);

                if ($fromN->empty || $toN->empty) {
                    return [];
                }
            }

            return array_values(array_filter(
                array_map($getName, Collection::range($from, $to)),
            ));
        };
    }

    /**
     * Returns a function to get note name from scale degree (1-based)
     *
     * @param string|array{0: string, 1: string} $scaleName Scale name
     * @return callable(int): string
     *
     * @example
     * array_map(Scale::degrees("C major"), [1, 2, 3]) // => ["C", "D", "E"]
     */
    public static function degrees(string|array $scaleName): callable
    {
        $scale = self::get($scaleName);
        $transpose = PitchDistance::tonicIntervalsTransposer($scale->intervals, $scale->tonic);

        return fn (int $degree): string =>
            $degree !== 0 ? $transpose($degree > 0 ? $degree - 1 : $degree) : '';
    }

    /**
     * Returns a function to get note name from scale step (0-based)
     *
     * @param string|array{0: string, 1: string} $scaleName Scale name
     * @return callable(int): string
     *
     * @example
     * array_map(Scale::steps("C4 major"), [0, 1, 2]) // => ["C4", "D4", "E4"]
     */
    public static function steps(string|array $scaleName): callable
    {
        $scale = self::get($scaleName);

        return PitchDistance::tonicIntervalsTransposer($scale->intervals, $scale->tonic);
    }

    /**
     * Create a function that maps note/midi to scale note name
     *
     * @param string|array<string> $scale Scale name or notes
     * @return callable(string|int): ?string
     */
    private static function getNoteNameOf(string|array $scale): callable
    {
        $names = is_array($scale)
            ? self::scaleNotes($scale)
            : self::get($scale)->notes;

        if (empty($names)) {
            return fn (string|int $noteOrMidi): ?string => null;
        }

        $chromas = array_map(fn (string $name) => PitchNote::note($name)->chroma, $names);

        return function (string|int $noteOrMidi) use ($names, $chromas): ?string {
            $currNote = is_int($noteOrMidi)
                ? PitchNote::note(Note::fromMidi($noteOrMidi))
                : PitchNote::note($noteOrMidi);

            $height = $currNote->height;

            if ($currNote->empty) {
                return null;
            }

            $chroma = $height % 12;
            if ($chroma < 0) {
                $chroma += 12;
            }

            $position = array_search($chroma, $chromas, true);

            if ($position === false) {
                return null;
            }

            return Note::enharmonic($currNote->name, $names[$position]);
        };
    }

    /**
     * Create empty scale
     */
    private static function empty(): ScaleObject
    {
        return new ScaleObject(
            empty: true,
            name: '',
            type: '',
            tonic: null,
            setNum: 0,
            chroma: '',
            normalized: '',
            aliases: [],
            notes: [],
            intervals: [],
        );
    }
}
