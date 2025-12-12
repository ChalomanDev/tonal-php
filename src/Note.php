<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Operations on musical notes
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/note
 */
final class Note
{
    /**
     * Natural note names
     */
    private const array NAMES = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];

    /**
     * Return the natural note names without octave
     *
     * @param array<mixed>|null $array Optional array of notes to filter
     * @return array<string>
     *
     * @example
     * Note::names() // => ["C", "D", "E", "F", "G", "A", "B"]
     * Note::names(["fx", "bb", 12, "nothing"]) // => ["F##", "Bb"]
     */
    public static function names(?array $array = null): array
    {
        if ($array === null) {
            return self::NAMES;
        }

        return array_values(
            array_map(
                fn (PitchNote $n) => $n->name,
                array_filter(
                    array_map(fn ($item) => is_string($item) ? PitchNote::note($item) : PitchNote::note(''), $array),
                    fn (PitchNote $n) => !$n->empty,
                ),
            ),
        );
    }

    /**
     * Get a note from a note name
     *
     * @example
     * Note::get('Bb4') // => PitchNote with name="Bb4", midi=70, chroma=10, etc.
     */
    public static function get(string $note): PitchNote
    {
        return PitchNote::note($note);
    }

    /**
     * Get the note name
     *
     * @example
     * Note::name('db') // => "Db"
     */
    public static function name(string $note): string
    {
        return self::get($note)->name;
    }

    /**
     * Get the note pitch class name
     *
     * @example
     * Note::pitchClass('Ax4') // => "A##"
     */
    public static function pitchClass(string $note): string
    {
        return self::get($note)->pc;
    }

    /**
     * Get the note accidentals
     *
     * @example
     * Note::accidentals('Bb4') // => "b"
     */
    public static function accidentals(string $note): string
    {
        return self::get($note)->acc;
    }

    /**
     * Get the note octave
     *
     * @example
     * Note::octave('C4') // => 4
     */
    public static function octave(string $note): ?int
    {
        return self::get($note)->oct;
    }

    /**
     * Get the note MIDI number
     *
     * @example
     * Note::midi('db4') // => 61
     */
    public static function midi(string $note): ?int
    {
        return self::get($note)->midi;
    }

    /**
     * Get the note frequency in Hz
     *
     * @example
     * Note::freq('A4') // => 440.0
     */
    public static function freq(string $note): ?float
    {
        return self::get($note)->freq;
    }

    /**
     * Get the note chroma (0-11)
     *
     * @example
     * Note::chroma('db4') // => 1
     */
    public static function chroma(string $note): int
    {
        return self::get($note)->chroma;
    }

    /**
     * Given a MIDI number, returns a note name (uses flats for altered notes)
     *
     * @example
     * Note::fromMidi(61) // => "Db4"
     * Note::fromMidi(61.7) // => "D4"
     */
    public static function fromMidi(int|float $midi): string
    {
        return Midi::midiToNoteName($midi);
    }

    /**
     * Given a MIDI number, returns a note name (uses sharps for altered notes)
     *
     * @example
     * Note::fromMidiSharps(61) // => "C#4"
     */
    public static function fromMidiSharps(int|float $midi): string
    {
        return Midi::midiToNoteName($midi, ['sharps' => true]);
    }

    /**
     * Given a frequency in Hz, returns a note name (uses flats for altered notes)
     *
     * @example
     * Note::fromFreq(440) // => "A4"
     */
    public static function fromFreq(float $freq): string
    {
        if ($freq <= 0 || is_nan($freq)) {
            return '';
        }

        return Midi::midiToNoteName(Midi::freqToMidi($freq));
    }

    /**
     * Given a frequency in Hz, returns a note name (uses sharps for altered notes)
     *
     * @example
     * Note::fromFreqSharps(470) // => "A#4"
     */
    public static function fromFreqSharps(float $freq): string
    {
        if ($freq <= 0 || is_nan($freq)) {
            return '';
        }

        return Midi::midiToNoteName(Midi::freqToMidi($freq), ['sharps' => true]);
    }

    /**
     * Find interval between two notes
     *
     * @example
     * Note::distance('C4', 'G4') // => "5P"
     */
    public static function distance(string $from, string $to): string
    {
        return PitchDistance::distance($from, $to);
    }

    /**
     * Transpose a note by an interval
     *
     * @param string $note The note name
     * @param string|array{0: int, 1: int} $interval The interval name or coordinates
     *
     * @example
     * Note::transpose('A4', '3M') // => "C#5"
     */
    public static function transpose(string $note, string|array $interval): string
    {
        return PitchDistance::transpose($note, $interval);
    }

    /**
     * Alias for transpose()
     *
     * @param string|array{0: int, 1: int} $interval
     */
    public static function tr(string $note, string|array $interval): string
    {
        return self::transpose($note, $interval);
    }

    /**
     * Returns a function that transposes by the given interval
     *
     * @param string $interval The interval to transpose by
     * @return callable(string): string
     *
     * @example
     * array_map(Note::transposeBy('5P'), ['C', 'D', 'E']) // => ["G", "A", "B"]
     */
    public static function transposeBy(string $interval): callable
    {
        return fn (string $note): string => self::transpose($note, $interval);
    }

    /**
     * Alias for transposeBy()
     */
    public static function trBy(string $interval): callable
    {
        return self::transposeBy($interval);
    }

    /**
     * Returns a function that transposes the note by an interval
     *
     * @param string $note The note to transpose from
     * @return callable(string): string
     *
     * @example
     * array_map(Note::transposeFrom('C'), ['1P', '3M', '5P']) // => ["C", "E", "G"]
     */
    public static function transposeFrom(string $note): callable
    {
        return fn (string $interval): string => self::transpose($note, $interval);
    }

    /**
     * Alias for transposeFrom()
     */
    public static function trFrom(string $note): callable
    {
        return self::transposeFrom($note);
    }

    /**
     * Transpose a note by a number of perfect fifths
     *
     * @example
     * Note::transposeFifths('G4', 1) // => "D5"
     * Note::transposeFifths('C', 2) // => "D"
     */
    public static function transposeFifths(string $note, int $fifths): string
    {
        return self::transpose($note, [$fifths, 0]);
    }

    /**
     * Alias for transposeFifths()
     */
    public static function trFifths(string $note, int $fifths): string
    {
        return self::transposeFifths($note, $fifths);
    }

    /**
     * Transpose a note by a number of octaves
     *
     * @example
     * Note::transposeOctaves('C4', 1) // => "C5"
     * Note::transposeOctaves('C4', -2) // => "C2"
     */
    public static function transposeOctaves(string $note, int $octaves): string
    {
        return self::transpose($note, [0, $octaves]);
    }

    /**
     * Comparator for ascending order
     *
     * @return callable(PitchNote, PitchNote): int
     */
    public static function ascending(): callable
    {
        return fn (PitchNote $a, PitchNote $b): int => $a->height - $b->height;
    }

    /**
     * Comparator for descending order
     *
     * @return callable(PitchNote, PitchNote): int
     */
    public static function descending(): callable
    {
        return fn (PitchNote $a, PitchNote $b): int => $b->height - $a->height;
    }

    /**
     * Sort notes by height
     *
     * @param array<mixed> $notes Notes to sort
     * @param callable|null $comparator Optional comparator function
     * @return array<string>
     *
     * @example
     * Note::sortedNames(['c', 'f', 'g', 'a', 'b']) // => ["C", "F", "G", "A", "B"]
     */
    public static function sortedNames(array $notes, ?callable $comparator = null): array
    {
        $comparator ??= self::ascending();
        $parsed = self::onlyNotes($notes);
        usort($parsed, $comparator);

        return array_map(fn (PitchNote $n) => $n->name, $parsed);
    }

    /**
     * Sort notes by height and remove duplicates
     *
     * @param array<mixed> $notes Notes to sort
     * @return array<string>
     *
     * @example
     * Note::sortedUniqNames(['a', 'b', 'c2', 'c2', 'b', 'c', 'c3']) // => ["C", "A", "B", "C2", "C3"]
     */
    public static function sortedUniqNames(array $notes): array
    {
        $sorted = self::sortedNames($notes, self::ascending());

        return array_values(
            array_filter($sorted, fn ($n, $i) => $i === 0 || $n !== $sorted[$i - 1], ARRAY_FILTER_USE_BOTH),
        );
    }

    /**
     * Simplify a note (use standard accidentals)
     *
     * @param string $note The note to simplify
     * @return string The simplified note name
     *
     * @example
     * Note::simplify('C##') // => "D"
     * Note::simplify('C###') // => "D#"
     * Note::simplify('B#4') // => "C5"
     */
    public static function simplify(string $note): string
    {
        $n = self::get($note);

        if ($n->empty) {
            return '';
        }

        return Midi::midiToNoteName($n->midi ?? $n->chroma, [
            'sharps' => $n->alt > 0,
            'pitchClass' => $n->midi === null,
        ]);
    }

    /**
     * Get enharmonic of a note
     *
     * @param string $note The note name
     * @param string|null $destName Optional destination pitch class
     * @return string The enharmonic note name
     *
     * @example
     * Note::enharmonic('Db') // => "C#"
     * Note::enharmonic('C') // => "C"
     * Note::enharmonic('F2', 'E#') // => "E#2"
     */
    public static function enharmonic(string $note, ?string $destName = null): string
    {
        $src = self::get($note);

        if ($src->empty) {
            return '';
        }

        // Destination: use given or generate one
        $destPc = $destName ?? Midi::midiToNoteName($src->midi ?? $src->chroma, [
            'sharps' => $src->alt < 0,
            'pitchClass' => true,
        ]);

        $dest = self::get($destPc);

        // Ensure destination is valid
        if ($dest->empty || $dest->chroma !== $src->chroma) {
            return '';
        }

        // If src has no octave, no need to calculate anything else
        if ($src->oct === null) {
            return $dest->pc;
        }

        // Detect any octave overflow
        $srcChroma = $src->chroma - $src->alt;
        $destChroma = $dest->chroma - $dest->alt;

        $destOctOffset = 0;
        if ($srcChroma > 11 || $destChroma < 0) {
            $destOctOffset = -1;
        } elseif ($srcChroma < 0 || $destChroma > 11) {
            $destOctOffset = 1;
        }

        // Calculate the new octave
        $destOct = $src->oct + $destOctOffset;

        return $dest->pc . $destOct;
    }

    /**
     * Filter only valid notes from an array
     *
     * @param array<mixed> $array
     * @return array<PitchNote>
     */
    private static function onlyNotes(array $array): array
    {
        return array_values(
            array_filter(
                array_map(
                    fn ($item) => is_string($item) ? PitchNote::note($item) : PitchNote::note(''),
                    $array,
                ),
                fn (PitchNote $n) => !$n->empty,
            ),
        );
    }
}
