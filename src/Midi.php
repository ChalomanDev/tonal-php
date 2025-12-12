<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * MIDI utilities
 */
final class Midi
{
    /**
     * Sharp note names
     */
    private const array SHARPS = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

    /**
     * Flat note names
     */
    private const array FLATS = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

    /**
     * Natural log of 2
     */
    private const float L2 = 0.6931471805599453; // log(2)

    /**
     * Natural log of 440
     */
    private const float L440 = 6.086775018525978; // log(440)

    /**
     * Check if a value is a valid MIDI number (0-127)
     */
    public static function isMidi(mixed $arg): bool
    {
        if (!is_numeric($arg)) {
            return false;
        }

        $val = (float) $arg;

        return $val >= 0 && $val <= 127;
    }

    /**
     * Get the note MIDI number (0-127)
     *
     * Returns null if not a valid note name or MIDI number
     *
     * @param string|int|float $note The note name or MIDI number
     */
    public static function toMidi(string|int|float $note): ?int
    {
        if (is_numeric($note) && self::isMidi($note)) {
            return (int) $note;
        }

        if (is_string($note)) {
            // Check if it's a numeric string first
            if (is_numeric($note) && self::isMidi($note)) {
                return (int) $note;
            }

            $n = PitchNote::note($note);

            return $n->empty ? null : $n->midi;
        }

        return null;
    }

    /**
     * Get the frequency in hertz from MIDI number
     *
     * @param int|float $midi The note MIDI number
     * @param float $tuning A4 tuning frequency in Hz (440 by default)
     */
    public static function midiToFreq(int|float $midi, float $tuning = 440.0): float
    {
        return pow(2, ($midi - 69) / 12) * $tuning;
    }

    /**
     * Get the MIDI number from a frequency in hertz
     *
     * The MIDI number can contain decimals (with two digits precision)
     *
     * @param float $freq The frequency in Hz
     */
    public static function freqToMidi(float $freq): float
    {
        $v = (12 * (log($freq) - self::L440)) / self::L2 + 69;

        return round($v * 100) / 100;
    }

    /**
     * Get pitch class (0-11) from MIDI number
     */
    public static function chroma(int $midi): int
    {
        return $midi % 12;
    }

    /**
     * Given a MIDI number, returns a note name
     *
     * The altered notes will have flats unless sharps option is set
     *
     * @param int|float $midi The MIDI note number
     * @param array{pitchClass?: bool, sharps?: bool} $options Options
     */
    public static function midiToNoteName(int|float $midi, array $options = []): string
    {
        if (is_nan($midi) || is_infinite($midi)) {
            return '';
        }

        $midi = (int) round($midi);
        $pcs = ($options['sharps'] ?? false) ? self::SHARPS : self::FLATS;
        $pc = $pcs[$midi % 12];

        if ($options['pitchClass'] ?? false) {
            return $pc;
        }

        $octave = (int) floor($midi / 12) - 1;

        return $pc . $octave;
    }

    /**
     * Get pitch class set from chroma string or MIDI numbers
     *
     * @param array<int>|string $notes List of MIDI numbers or chroma string
     * @return array<int> Sorted unique chroma numbers
     */
    public static function pcset(array|string $notes): array
    {
        if (is_string($notes)) {
            return self::pcsetFromChroma($notes);
        }

        return self::pcsetFromMidi($notes);
    }

    /**
     * Returns a function that finds the nearest MIDI note of a pitch class set
     *
     * @param array<int>|string $notes List of MIDI numbers or chroma string
     * @return callable(int): ?int
     */
    public static function pcsetNearest(array|string $notes): callable
    {
        $set = self::pcset($notes);

        return function (int $midi) use ($set): ?int {
            if (empty($set)) {
                return null;
            }

            $ch = self::chroma($midi);

            for ($i = 0; $i < 12; $i++) {
                $chUp = ($ch + $i) % 12;
                $chDown = ($ch - $i + 12) % 12;

                if (in_array($chUp, $set, true)) {
                    return $midi + $i;
                }

                if (in_array($chDown, $set, true)) {
                    return $midi - $i;
                }
            }

            return null;
        };
    }

    /**
     * Returns a function to map a pitch class set over any note by step
     *
     * @param array<int>|string $notes List of MIDI numbers or chroma string
     * @param int $tonic The tonic MIDI number
     * @return callable(int): int
     */
    public static function pcsetSteps(array|string $notes, int $tonic): callable
    {
        $set = self::pcset($notes);
        $len = count($set);

        return function (int $step) use ($set, $len, $tonic): int {
            if ($len === 0) {
                return $tonic;
            }

            $index = $step < 0
                ? ($len - (abs($step) % $len)) % $len
                : $step % $len;

            $octaves = (int) floor($step / $len);

            return $set[$index] + $octaves * 12 + $tonic;
        };
    }

    /**
     * Returns a function to map a pitch class set over any note by degree
     *
     * Same as pcsetSteps, but returns 1 for the first step
     *
     * @param array<int>|string $notes List of MIDI numbers or chroma string
     * @param int $tonic The tonic MIDI number
     * @return callable(int): ?int
     */
    public static function pcsetDegrees(array|string $notes, int $tonic): callable
    {
        $steps = self::pcsetSteps($notes, $tonic);

        return function (int $degree) use ($steps): ?int {
            if ($degree === 0) {
                return null;
            }

            return $steps($degree > 0 ? $degree - 1 : $degree);
        };
    }

    /**
     * Create pitch class set from chroma string
     *
     * @return array<int>
     */
    private static function pcsetFromChroma(string $chroma): array
    {
        $pcset = [];

        for ($i = 0; $i < min(strlen($chroma), 12); $i++) {
            if ($chroma[$i] === '1') {
                $pcset[] = $i;
            }
        }

        return $pcset;
    }

    /**
     * Create pitch class set from MIDI numbers
     *
     * @param array<int> $midi
     * @return array<int>
     */
    private static function pcsetFromMidi(array $midi): array
    {
        $chromas = array_map(fn($m) => self::chroma($m), $midi);
        sort($chromas);

        return array_values(array_unique($chromas));
    }
}
