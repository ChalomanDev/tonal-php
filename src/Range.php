<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Range utilities for creating note ranges
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/range
 */
final class Range
{
    /**
     * Create a numeric range from notes or MIDI numbers.
     * The array items are connected to create complex ranges.
     *
     * @param array<string|int> $notes The list of notes or MIDI numbers
     * @return array<int> An array of MIDI numbers
     *
     * @example
     * Range::numeric(['C5', 'C4']) // => [72, 71, 70, 69, 68, 67, 66, 65, 64, 63, 62, 61, 60]
     * Range::numeric([10, 5]) // => [10, 9, 8, 7, 6, 5]
     * Range::numeric(['C4', 'E4', 'Bb3']) // => [60, 61, 62, 63, 64, 63, 62, 61, 60, 59, 58]
     */
    public static function numeric(array $notes): array
    {
        if (empty($notes)) {
            return [];
        }

        // Convert notes to MIDI numbers
        $midi = Collection::compact(array_map(
            fn (string|int $note): ?int => is_int($note) ? $note : Midi::toMidi($note),
            $notes,
        ));

        // Check if all notes were valid
        if (count($midi) !== count($notes)) {
            return [];
        }

        // Build the range by connecting consecutive notes
        $result = [$midi[0]];

        for ($i = 1; $i < count($midi); $i++) {
            $last = $result[count($result) - 1];
            $rangeToAdd = Collection::range($last, $midi[$i]);
            // Remove first element (already in result) and merge
            array_shift($rangeToAdd);
            $result = array_merge($result, $rangeToAdd);
        }

        return $result;
    }

    /**
     * Create a range of chromatic notes. The altered notes will use flats by default.
     *
     * @param array<string|int> $notes The list of notes or MIDI numbers
     * @param array{sharps?: bool, pitchClass?: bool} $options Options for note naming
     * @return array<string> An array of note names
     *
     * @example
     * Range::chromatic(['C2', 'E2', 'D2']) // => ['C2', 'Db2', 'D2', 'Eb2', 'E2', 'Eb2', 'D2']
     * Range::chromatic(['C2', 'C3'], ['sharps' => true]) // => ['C2', 'C#2', 'D2', ...]
     */
    public static function chromatic(array $notes, array $options = []): array
    {
        return array_map(
            fn (int $midi): string => Midi::midiToNoteName($midi, $options),
            self::numeric($notes),
        );
    }
}
