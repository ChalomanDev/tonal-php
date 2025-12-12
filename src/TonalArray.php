<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Array utilities for working with notes
 *
 * This class extends the basic Collection utilities with music-specific
 * operations for sorting and filtering note names.
 */
final class TonalArray
{
    /**
     * Sort an array of notes in ascending order.
     * Pitch classes are listed before notes (notes without octave).
     * Any string that is not a valid note is removed.
     *
     * @param array<string> $notes Array of note names
     * @return array<string> Sorted array of note names
     *
     * @example sortedNoteNames(['c2', 'c5', 'c1', 'c0', 'c6', 'c']) => ['C', 'C0', 'C1', 'C2', 'C5', 'C6']
     * @example sortedNoteNames(['c', 'F', 'G', 'a', 'b', 'h', 'J']) => ['C', 'F', 'G', 'A', 'B']
     */
    public static function sortedNoteNames(array $notes): array
    {
        // Parse notes and filter out invalid ones
        $valid = [];
        foreach ($notes as $n) {
            $note = PitchNote::note($n);
            if (!$note->empty) {
                $valid[] = $note;
            }
        }

        // Sort by height
        usort($valid, fn(PitchNote $a, PitchNote $b) => $a->height <=> $b->height);

        // Return names
        return array_map(fn(PitchNote $n) => $n->name, $valid);
    }

    /**
     * Get sorted notes with duplicates removed.
     * Pitch classes are listed before notes.
     *
     * @param array<string> $notes Array of note names
     * @return array<string> Unique sorted notes
     *
     * @example sortedUniqNoteNames(['a', 'b', 'c2', '1p', 'p2', 'c2', 'b', 'c', 'c3']) => ['C', 'A', 'B', 'C2', 'C3']
     */
    public static function sortedUniqNoteNames(array $notes): array
    {
        $sorted = self::sortedNoteNames($notes);

        // Filter duplicates while keeping order
        $result = [];
        $prev = null;
        foreach ($sorted as $note) {
            if ($note !== $prev) {
                $result[] = $note;
                $prev = $note;
            }
        }

        return $result;
    }

    /**
     * Creates a numeric range (delegates to Collection)
     *
     * @return array<int>
     *
     * @example range(-2, 2) // => [-2, -1, 0, 1, 2]
     * @example range(2, -2) // => [2, 1, 0, -1, -2]
     */
    public static function range(int $from, int $to): array
    {
        return Collection::range($from, $to);
    }

    /**
     * Rotates a list a number of times (delegates to Collection)
     *
     * @template T
     * @param int $times Number of rotations
     * @param array<T> $arr The array to rotate
     * @return array<T> The rotated array
     *
     * @example rotate(2, ['a', 'b', 'c', 'd', 'e']) => ['c', 'd', 'e', 'a', 'b']
     */
    public static function rotate(int $times, array $arr): array
    {
        return Collection::rotate($times, $arr);
    }

    /**
     * Return a copy of the array with null/false/empty values removed
     * Keeps 0 and non-empty values (delegates to Collection)
     *
     * @param array<mixed> $arr
     * @return array<mixed>
     *
     * @example compact(['a', 1, 0, true, false, null]) => ['a', 1, 0, true]
     */
    public static function compact(array $arr): array
    {
        return Collection::compact($arr);
    }

    /**
     * Randomizes the order of the array (delegates to Collection)
     *
     * @template T
     * @param array<T> $arr The array to shuffle
     * @param callable|null $rnd Optional random function returning float 0-1
     * @return array<T> The shuffled array
     *
     * @example shuffle(['a', 'b', 'c', 'd'], fn() => 0.2) => ['b', 'c', 'd', 'a']
     */
    public static function shuffle(array $arr, ?callable $rnd = null): array
    {
        return Collection::shuffle($arr, $rnd);
    }

    /**
     * Get all permutations of an array (delegates to Collection)
     *
     * @template T
     * @param array<T> $arr The array
     * @return array<array<T>> All permutations
     *
     * @example permutations(['a', 'b', 'c']) => [['a','b','c'], ['b','a','c'], ...]
     */
    public static function permutations(array $arr): array
    {
        return Collection::permutations($arr);
    }
}
