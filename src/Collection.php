<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Collection utilities for working with arrays
 */
final class Collection
{
    /**
     * Creates a numeric range
     *
     * @return array<int>
     *
     * @example range(-2, 2) // => [-2, -1, 0, 1, 2]
     * @example range(2, -2) // => [2, 1, 0, -1, -2]
     */
    public static function range(int $from, int $to): array
    {
        if ($from < $to) {
            return self::ascendingRange($from, $to - $from + 1);
        }

        return self::descendingRange($from, $from - $to + 1);
    }

    /**
     * Rotates a list a number of times
     *
     * @template T
     * @param int $times Number of rotations
     * @param array<T> $arr The array to rotate
     * @return array<T> The rotated array
     *
     * @example rotate(1, [1, 2, 3]) // => [2, 3, 1]
     */
    public static function rotate(int $times, array $arr): array
    {
        $len = count($arr);

        if ($len === 0) {
            return [];
        }

        $n = (($times % $len) + $len) % $len;

        return array_merge(
            array_slice($arr, $n),
            array_slice($arr, 0, $n),
        );
    }

    /**
     * Return a copy of the array with null/false/empty values removed
     * Keeps 0 and non-empty values
     *
     * @param array<mixed> $arr
     * @return array<mixed>
     *
     * @example compact(["a", "b", null, "c"]) // => ["a", "b", "c"]
     */
    public static function compact(array $arr): array
    {
        return array_values(array_filter($arr, fn ($n) => $n === 0 || $n));
    }

    /**
     * Randomizes the order of the array using the Fisher-Yates shuffle
     *
     * @template T
     * @param array<T> $arr The array to shuffle
     * @param callable|null $rnd Optional random function returning float 0-1
     * @return array<T> The shuffled array
     *
     * @example shuffle(["C", "D", "E", "F"]) // => [...]
     */
    public static function shuffle(array $arr, ?callable $rnd = null): array
    {
        $rnd = $rnd ?? fn () => mt_rand() / mt_getrandmax();
        $result = array_values($arr);
        $m = count($result);

        while ($m > 0) {
            $i = (int) floor($rnd() * $m);
            $m--;
            $t = $result[$m];
            $result[$m] = $result[$i];
            $result[$i] = $t;
        }

        return $result;
    }

    /**
     * Get all permutations of an array
     *
     * @template T
     * @param array<T> $arr The array
     * @return array<array<T>> All permutations
     *
     * @example permutations(["a", "b", "c"]) // => [["a","b","c"], ["b","a","c"], ...]
     */
    public static function permutations(array $arr): array
    {
        if (count($arr) === 0) {
            return [[]];
        }

        $first = $arr[0];
        $rest = array_slice($arr, 1);
        $perms = self::permutations($rest);

        $result = [];
        foreach ($perms as $perm) {
            for ($pos = 0; $pos <= count($perm); $pos++) {
                $newPerm = $perm;
                array_splice($newPerm, $pos, 0, [$first]);
                $result[] = $newPerm;
            }
        }

        return $result;
    }

    /**
     * Create ascending range
     *
     * @return array<int>
     */
    private static function ascendingRange(int $start, int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $start + $i;
        }

        return $result;
    }

    /**
     * Create descending range
     *
     * @return array<int>
     */
    private static function descendingRange(int $start, int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $start - $i;
        }

        return $result;
    }
}
