<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Tonal - Main aggregator class for the Tonal PHP library.
 *
 * This class provides convenient access to all Tonal modules.
 * It serves as the main entry point for the library.
 *
 * Example usage:
 * ```php
 * use Chaloman\Tonal\Tonal;
 *
 * // Access modules directly
 * $note = Tonal::note('C4');
 * $chord = Tonal::chord('Cmaj7');
 * $scale = Tonal::scale('C major');
 *
 * // Or use the module classes directly
 * $note = Note::get('C4');
 * ```
 *
 * @see https://github.com/tonaljs/tonal Original TypeScript library
 */
final class Tonal
{
    /**
     * Library version
     */
    public const VERSION = '1.0.0';

    // =========================================================================
    // Core Functions (re-exported from Core)
    // =========================================================================

    /**
     * Transpose a note by an interval.
     *
     * @param string $note The note to transpose
     * @param string|array $interval The interval to transpose by
     * @return string The transposed note
     */
    public static function transpose(string $note, string|array $interval): string
    {
        return Core::transpose($note, $interval);
    }

    /**
     * Find the interval distance between two notes.
     *
     * @param string $from The starting note
     * @param string $to The ending note
     * @return string The interval between the notes
     */
    public static function distance(string $from, string $to): string
    {
        return Core::distance($from, $to);
    }

    // =========================================================================
    // Note Functions
    // =========================================================================

    /**
     * Get note properties.
     *
     * @param string $name The note name
     * @return PitchNote The note object
     */
    public static function note(string $name): PitchNote
    {
        return Note::get($name);
    }

    // =========================================================================
    // Interval Functions
    // =========================================================================

    /**
     * Get interval properties.
     *
     * @param string $name The interval name
     * @return PitchInterval The interval object
     */
    public static function interval(string $name): PitchInterval
    {
        return Interval::get($name);
    }

    // =========================================================================
    // Chord Functions
    // =========================================================================

    /**
     * Get chord properties.
     *
     * @param string $name The chord name (e.g., "Cmaj7", "Dm/F")
     * @return ChordObject The chord object
     */
    public static function chord(string $name): ChordObject
    {
        return Chord::get($name);
    }

    /**
     * Detect chords from a list of notes.
     *
     * @param array<string> $notes The notes to detect
     * @return array<string> Possible chord names
     */
    public static function chordDetect(array $notes): array
    {
        return ChordDetect::detect($notes);
    }

    // =========================================================================
    // Scale Functions
    // =========================================================================

    /**
     * Get scale properties.
     *
     * @param string $name The scale name (e.g., "C major")
     * @return ScaleObject The scale object
     */
    public static function scale(string $name): ScaleObject
    {
        return Scale::get($name);
    }

    // =========================================================================
    // Key Functions
    // =========================================================================

    /**
     * Get major key properties.
     *
     * @param string $tonic The tonic note
     * @return MajorKey The major key object
     */
    public static function majorKey(string $tonic): MajorKey
    {
        return Key::majorKey($tonic);
    }

    /**
     * Get minor key properties.
     *
     * @param string $tonic The tonic note
     * @return MinorKey The minor key object
     */
    public static function minorKey(string $tonic): MinorKey
    {
        return Key::minorKey($tonic);
    }

    // =========================================================================
    // MIDI Functions
    // =========================================================================

    /**
     * Convert note name to MIDI number.
     *
     * @param string|int $note The note name or MIDI number
     * @return int|null The MIDI number or null if invalid
     */
    public static function toMidi(string|int $note): ?int
    {
        return Midi::toMidi($note);
    }

    /**
     * Convert MIDI number to note name.
     *
     * @param int $midi The MIDI number
     * @param array{sharps?: bool, pitchClass?: bool} $options Conversion options
     * @return string The note name
     */
    public static function midiToNoteName(int $midi, array $options = []): string
    {
        return Midi::midiToNoteName($midi, $options);
    }

    // =========================================================================
    // Roman Numeral Functions
    // =========================================================================

    /**
     * Get roman numeral properties.
     *
     * @param string $name The roman numeral (e.g., "IV", "viio")
     * @return RomanNumeral The roman numeral object
     */
    public static function romanNumeral(string $name): RomanNumeral
    {
        return RomanNumeral::get($name);
    }

    // =========================================================================
    // Progression Functions
    // =========================================================================

    /**
     * Convert a roman numeral progression to chord names.
     *
     * @param array<string> $numerals The roman numerals
     * @param string $tonic The tonic note
     * @return array<string> The chord names
     */
    public static function fromRomanNumerals(string $tonic, array $numerals): array
    {
        return Progression::fromRomanNumerals($tonic, $numerals);
    }

    /**
     * Convert chord names to roman numerals.
     *
     * @param string $tonic The tonic note
     * @param array<string> $chords The chord names
     * @return array<string> The roman numerals
     */
    public static function toRomanNumerals(string $tonic, array $chords): array
    {
        return Progression::toRomanNumerals($tonic, $chords);
    }

    // =========================================================================
    // Module Access (for advanced usage)
    // =========================================================================

    /**
     * Get a list of all available module classes.
     *
     * @return array<string, string> Module name => Class name mapping
     */
    public static function modules(): array
    {
        return [
            'AbcNotation' => AbcNotation::class,
            'Chord' => Chord::class,
            'ChordDetect' => ChordDetect::class,
            'ChordType' => ChordType::class,
            'Collection' => Collection::class,
            'Core' => Core::class,
            'DurationValue' => DurationValue::class,
            'Interval' => Interval::class,
            'Key' => Key::class,
            'Midi' => Midi::class,
            'Mode' => Mode::class,
            'Note' => Note::class,
            'Pcset' => Pcset::class,
            'Pitch' => Pitch::class,
            'PitchDistance' => PitchDistance::class,
            'PitchInterval' => PitchInterval::class,
            'PitchNote' => PitchNote::class,
            'Progression' => Progression::class,
            'Range' => Range::class,
            'RhythmPattern' => RhythmPattern::class,
            'RomanNumeral' => RomanNumeral::class,
            'Scale' => Scale::class,
            'ScaleType' => ScaleType::class,
            'TimeSignature' => TimeSignature::class,
            'TonalArray' => TonalArray::class,
            'VoiceLeading' => VoiceLeading::class,
            'Voicing' => Voicing::class,
            'VoicingDictionary' => VoicingDictionary::class,
        ];
    }
}
