<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Functions for calculating distances and transposing notes/intervals
 */
final class PitchDistance
{
    /**
     * Transpose a note by an interval.
     *
     * @param string $noteName The note name (e.g., "C3", "D")
     * @param string|array{0: int, 1: int} $intervalName The interval name (e.g., "3M") or coordinates
     * @return string The transposed note name, or empty string if invalid
     *
     * @example
     * PitchDistance::transpose("d3", "3M") // => "F#3"
     * PitchDistance::transpose("D", "3M") // => "F#"
     */
    public static function transpose(string $noteName, string|array $intervalName): string
    {
        $note = PitchNote::note($noteName);

        $intervalCoord = is_array($intervalName)
            ? $intervalName
            : PitchInterval::interval($intervalName)->coord;

        if ($note->empty || empty($intervalCoord) || count($intervalCoord) < 2) {
            return '';
        }

        $noteCoord = $note->coord;

        // If note is a pitch class (only fifths coordinate)
        if (count($noteCoord) === 1) {
            $tr = [$noteCoord[0] + $intervalCoord[0]];
        } else {
            $tr = [
                $noteCoord[0] + $intervalCoord[0],
                $noteCoord[1] + $intervalCoord[1],
            ];
        }

        return PitchNote::coordToNote($tr)->name;
    }

    /**
     * Create a transposer function for a set of intervals from a tonic.
     *
     * @param array<string> $intervals The intervals to transpose
     * @param string|null $tonic The tonic note
     * @return callable(int): string A function that takes an index and returns the transposed note
     */
    public static function tonicIntervalsTransposer(array $intervals, ?string $tonic): callable
    {
        $len = count($intervals);

        return function (int $normalized) use ($intervals, $tonic, $len): string {
            if ($tonic === null || $tonic === '') {
                return '';
            }

            $index = $normalized < 0
                ? ($len - ((-$normalized) % $len)) % $len
                : $normalized % $len;

            $octaves = (int) floor($normalized / $len);
            $root = self::transpose($tonic, [0, $octaves]);

            return self::transpose($root, $intervals[$index]);
        };
    }

    /**
     * Find the interval distance between two notes.
     *
     * To find distance between pitch classes, both notes must be pitch classes
     * and the interval is always ascending.
     *
     * @param string $fromNote The note to calculate distance from
     * @param string $toNote The note to calculate distance to
     * @return string The interval name, or empty string if invalid notes
     */
    public static function distance(string $fromNote, string $toNote): string
    {
        $from = PitchNote::note($fromNote);
        $to = PitchNote::note($toNote);

        if ($from->empty || $to->empty) {
            return '';
        }

        $fcoord = $from->coord;
        $tcoord = $to->coord;

        $fifths = $tcoord[0] - $fcoord[0];

        // Calculate octaves based on whether notes have octave info
        if (count($fcoord) === 2 && count($tcoord) === 2) {
            $octs = $tcoord[1] - $fcoord[1];
        } else {
            $octs = (int) -floor(($fifths * 7) / 12);
        }

        // Edge case: unison in same octave can be descending (see #243 & #428)
        $forceDescending = $to->height === $from->height
            && $to->midi !== null
            && $from->oct === $to->oct
            && $from->step > $to->step;

        return PitchInterval::coordToInterval([$fifths, $octs], $forceDescending)->name;
    }
}
