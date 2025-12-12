<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Rhythm pattern utilities
 *
 * A rhythm pattern is an array of 0s and 1s representing beats and rests.
 */
final class RhythmPattern
{
    /**
     * Create a rhythm pattern from a number or concatenation of numbers in binary form
     *
     * @param int ...$numbers One or more numbers
     * @return array<int> An array of 0s and 1s representing the rhythm pattern
     *
     * @example binary(13) // => [1, 1, 0, 1]
     * @example binary(12, 13) // => [1, 1, 0, 0, 1, 1, 0, 1]
     */
    public static function binary(int ...$numbers): array
    {
        $pattern = [];

        foreach ($numbers as $number) {
            $binary = decbin($number);
            foreach (str_split($binary) as $digit) {
                $pattern[] = (int) $digit;
            }
        }

        return $pattern;
    }

    /**
     * Create a rhythmic pattern using hexadecimal numbers
     *
     * @param string $hexNumber String with the hexadecimal number
     * @return array<int> An array of 0s and 1s representing the rhythm pattern
     *
     * @example hex("8f") // => [1, 0, 0, 0, 1, 1, 1, 1]
     */
    public static function hex(string $hexNumber): array
    {
        $pattern = [];

        for ($i = 0; $i < strlen($hexNumber); $i++) {
            $digit = hexdec($hexNumber[$i]);
            $binary = str_pad(decbin((int) $digit), 4, '0', STR_PAD_LEFT);

            foreach (str_split($binary) as $bit) {
                $pattern[] = $bit === '1' ? 1 : 0;
            }
        }

        return $pattern;
    }

    /**
     * Create a rhythm pattern from the onsets
     *
     * @param int ...$numbers The onset sizes
     * @return array<int> An array of 0s and 1s representing the rhythm pattern
     *
     * @example onsets(1, 2, 2, 1) // => [1, 0, 1, 0, 0, 1, 0, 0, 1, 0]
     */
    public static function onsets(int ...$numbers): array
    {
        $pattern = [];

        foreach ($numbers as $number) {
            $pattern[] = 1;
            for ($i = 0; $i < $number; $i++) {
                $pattern[] = 0;
            }
        }

        return $pattern;
    }

    /**
     * Create a random rhythm pattern with a specified length
     *
     * @param int $length Length of the pattern
     * @param float $probability Threshold where random number is considered a beat (defaults to 0.5)
     * @param callable|null $rnd A random function (null defaults to random)
     * @return array<int> An array of 0s and 1s representing the rhythm pattern
     *
     * @example random(4) // => [1, 0, 0, 1]
     */
    public static function random(int $length, float $probability = 0.5, ?callable $rnd = null): array
    {
        $rnd = $rnd ?? fn() => mt_rand() / mt_getrandmax();
        $pattern = [];

        for ($i = 0; $i < $length; $i++) {
            $pattern[] = $rnd() >= $probability ? 1 : 0;
        }

        return $pattern;
    }

    /**
     * Create a rhythm pattern based on the given probability thresholds
     *
     * @param array<float> $probabilities An array with the probability of each step to be a beat
     * @param callable|null $rnd A random function (null defaults to random)
     * @return array<int> An array of 0s and 1s representing the rhythm pattern
     *
     * @example probability([0.6, 0, 0.2, 0.5]) // => [0, 0, 0, 1]
     */
    public static function probability(array $probabilities, ?callable $rnd = null): array
    {
        $rnd = $rnd ?? fn() => mt_rand() / mt_getrandmax();

        return array_map(
            fn($p) => $rnd() <= $p ? 1 : 0,
            $probabilities
        );
    }

    /**
     * Rotate a pattern right
     *
     * @param array<int> $pattern The pattern to rotate
     * @param int $rotations The number of steps to rotate
     * @return array<int> The rotated pattern (an array of 0s and 1s)
     *
     * @example rotate([1, 0, 0, 1], 2) // => [0, 1, 1, 0]
     */
    public static function rotate(array $pattern, int $rotations): array
    {
        $len = count($pattern);

        if ($len === 0) {
            return [];
        }

        $rotated = [];
        for ($i = 0; $i < $len; $i++) {
            $pos = ((($i - $rotations) % $len) + $len) % $len;
            $rotated[$i] = $pattern[$pos];
        }

        return $rotated;
    }

    /**
     * Generates an euclidean rhythm pattern
     *
     * @param int $steps The length of the pattern
     * @param int $beats The number of beats
     * @return array<int> An array with 0s and 1s representing the rhythmic pattern
     *
     * @example euclid(8, 3) // => [1, 0, 0, 1, 0, 0, 1, 0]
     */
    public static function euclid(int $steps, int $beats): array
    {
        $pattern = [];
        $d = -1;

        for ($i = 0; $i < $steps; $i++) {
            $v = (int) floor($i * ($beats / $steps));
            $pattern[$i] = $v !== $d ? 1 : 0;
            $d = $v;
        }

        return $pattern;
    }
}
