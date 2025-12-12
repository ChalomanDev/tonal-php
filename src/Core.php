<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

use Chaloman\Tonal\Contracts\PitchLike;

/**
 * Core module that provides essential music theory functions.
 *
 * This is an aggregator class that re-exports functions from:
 * - Pitch
 * - PitchDistance (transpose, distance)
 * - PitchInterval
 * - PitchNote
 *
 * It also provides utility functions.
 */
final class Core
{
    /**
     * Fill a string with repeated characters
     *
     * @param string $s The character to repeat
     * @param int $n The number of times to repeat (absolute value is used)
     * @return string The repeated string
     */
    public static function fillStr(string $s, int $n): string
    {
        return str_repeat($s, abs($n));
    }

    // Re-export from PitchDistance

    /**
     * Transpose a note by an interval.
     *
     * @param string|array{0: int, 1: int} $intervalName
     * @see PitchDistance::transpose()
     */
    public static function transpose(string $noteName, string|array $intervalName): string
    {
        return PitchDistance::transpose($noteName, $intervalName);
    }

    /**
     * Find the interval distance between two notes.
     *
     * @see PitchDistance::distance()
     */
    public static function distance(string $fromNote, string $toNote): string
    {
        return PitchDistance::distance($fromNote, $toNote);
    }

    // Re-export from PitchInterval

    /**
     * Get interval properties from a string or Pitch.
     *
     * @see PitchInterval::interval()
     */
    public static function interval(string|Pitch|PitchLike $src): PitchInterval
    {
        return PitchInterval::interval($src);
    }

    /**
     * Convert coordinates to interval.
     *
     * @param array{0: int, 1?: int} $coord
     * @see PitchInterval::coordToInterval()
     */
    public static function coordToInterval(array $coord, bool $forceDescending = false): PitchInterval
    {
        return PitchInterval::coordToInterval($coord, $forceDescending);
    }

    // Re-export from PitchNote

    /**
     * Get note properties from a string or Pitch.
     *
     * @see PitchNote::note()
     */
    public static function note(string|Pitch $src): PitchNote
    {
        return PitchNote::note($src);
    }

    /**
     * Convert coordinates to note.
     *
     * @param array{0: int, 1?: int} $noteCoord
     * @see PitchNote::coordToNote()
     */
    public static function coordToNote(array $noteCoord): PitchNote
    {
        return PitchNote::coordToNote($noteCoord);
    }

    // Re-export from Pitch

    /**
     * Check if a value is a valid Pitch.
     *
     * @see Pitch::isPitch()
     */
    public static function isPitch(mixed $pitch): bool
    {
        return Pitch::isPitch($pitch);
    }

    /**
     * Check if a value is a valid NamedPitch.
     *
     * @see Pitch::isNamedPitch()
     */
    public static function isNamedPitch(mixed $src): bool
    {
        return Pitch::isNamedPitch($src);
    }

    /**
     * Calculate the chroma (0-11) of a pitch.
     *
     * @see Pitch::chroma()
     */
    public static function chroma(Pitch $pitch): int
    {
        return Pitch::chroma($pitch);
    }

    /**
     * Calculate the height of a pitch.
     *
     * @see Pitch::height()
     */
    public static function height(Pitch $pitch): int
    {
        return Pitch::height($pitch);
    }

    /**
     * Calculate the MIDI number of a pitch.
     *
     * @see Pitch::midi()
     */
    public static function midi(Pitch $pitch): ?int
    {
        return Pitch::midi($pitch);
    }

    /**
     * Get coordinates from pitch object.
     *
     * @return array{0: int}|array{0: int, 1: int}
     * @see Pitch::coordinates()
     */
    public static function coordinates(Pitch $pitch): array
    {
        return Pitch::coordinates($pitch);
    }
}
