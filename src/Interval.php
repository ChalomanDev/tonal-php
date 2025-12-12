<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Operations on musical intervals
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/interval
 */
final class Interval
{
    /**
     * Interval numbers for semitones 0-11
     */
    private const array INTERVAL_NUMBERS = [1, 2, 2, 3, 3, 4, 5, 5, 6, 6, 7, 7];

    /**
     * Interval qualities for semitones 0-11
     */
    private const array INTERVAL_QUALITIES = ['P', 'm', 'M', 'm', 'M', 'P', 'd', 'P', 'm', 'M', 'm', 'M'];

    /**
     * Get the natural list of interval names
     *
     * @return array<string>
     */
    public static function names(): array
    {
        return ['1P', '2M', '3M', '4P', '5P', '6m', '7m'];
    }

    /**
     * Get properties of an interval
     *
     * @example
     * Interval::get('P4') // => PitchInterval object with name="4P", semitones=5, etc.
     */
    public static function get(string $name): PitchInterval
    {
        return PitchInterval::interval($name);
    }

    /**
     * Get name of an interval
     *
     * @example
     * Interval::name('4P') // => "4P"
     * Interval::name('P4') // => "4P"
     * Interval::name('C4') // => ""
     */
    public static function name(string $name): string
    {
        return self::get($name)->name;
    }

    /**
     * Get semitones of an interval
     *
     * @example
     * Interval::semitones('P4') // => 5
     */
    public static function semitones(string $name): int
    {
        return self::get($name)->semitones;
    }

    /**
     * Get quality of an interval
     *
     * @example
     * Interval::quality('P4') // => "P"
     */
    public static function quality(string $name): string
    {
        return self::get($name)->q;
    }

    /**
     * Get number of an interval
     *
     * @example
     * Interval::num('P4') // => 4
     */
    public static function num(string $name): int
    {
        return self::get($name)->num;
    }

    /**
     * Get the simplified version of an interval.
     *
     * @param string $name The interval name
     * @return string The simplified interval name
     *
     * @example
     * Interval::simplify("9M") // => "2M"
     * Interval::simplify("2M") // => "2M"
     * Interval::simplify("-2M") // => "-2M"
     */
    public static function simplify(string $name): string
    {
        $i = self::get($name);

        if ($i->empty) {
            return '';
        }

        return $i->simple . $i->q;
    }

    /**
     * Get the inversion of an interval.
     *
     * @see https://en.wikipedia.org/wiki/Inversion_(music)#Intervals
     *
     * @param string $name The interval name
     * @return string The inverted interval name
     *
     * @example
     * Interval::invert("3m") // => "6M"
     * Interval::invert("2M") // => "7m"
     */
    public static function invert(string $name): string
    {
        $i = self::get($name);

        if ($i->empty) {
            return '';
        }

        $step = (7 - $i->step) % 7;
        $alt = $i->type === IntervalType::Perfectable ? -$i->alt : -($i->alt + 1);

        return self::get(self::buildFromProps($step, $alt, $i->oct, $i->dir))->name;
    }

    /**
     * Get interval name from semitones number.
     *
     * Since there are several interval names for the same number,
     * the name is arbitrary but deterministic.
     *
     * @param int $semitones The number of semitones (can be negative)
     * @return string The interval name
     *
     * @example
     * Interval::fromSemitones(7) // => "5P"
     * Interval::fromSemitones(-7) // => "-5P"
     */
    public static function fromSemitones(int $semitones): string
    {
        $d = $semitones < 0 ? -1 : 1;
        $n = abs($semitones);
        $c = $n % 12;
        $o = (int) floor($n / 12);

        return ($d * (self::INTERVAL_NUMBERS[$c] + 7 * $o)) . self::INTERVAL_QUALITIES[$c];
    }

    /**
     * Find interval between two notes
     *
     * @example
     * Interval::distance("C4", "G4") // => "5P"
     */
    public static function distance(string $from, string $to): string
    {
        return PitchDistance::distance($from, $to);
    }

    /**
     * Add two intervals
     *
     * @param string $a First interval
     * @param string $b Second interval
     * @return string The resulting interval name, or empty string if invalid
     *
     * @example
     * Interval::add("3m", "5P") // => "7m"
     */
    public static function add(string $a, string $b): string
    {
        $coordA = self::get($a)->coord;
        $coordB = self::get($b)->coord;

        if (empty($coordA) || empty($coordB)) {
            return '';
        }

        $coord = [$coordA[0] + $coordB[0], $coordA[1] + $coordB[1]];

        return PitchInterval::coordToInterval($coord)->name;
    }

    /**
     * Returns a function that adds an interval
     *
     * @param string $interval The interval to add
     * @return callable(string): string A function that adds the interval
     *
     * @example
     * $addFifth = Interval::addTo('5P');
     * $addFifth('1P') // => "5P"
     * $addFifth('2M') // => "6M"
     */
    public static function addTo(string $interval): callable
    {
        return fn(string $other): string => self::add($interval, $other);
    }

    /**
     * Subtract two intervals
     *
     * @param string $a Minuend interval
     * @param string $b Subtrahend interval
     * @return string The resulting interval name, or empty string if invalid
     *
     * @example
     * Interval::subtract('5P', '3M') // => '3m'
     * Interval::subtract('3M', '5P') // => '-3m'
     */
    public static function subtract(string $a, string $b): string
    {
        $coordA = self::get($a)->coord;
        $coordB = self::get($b)->coord;

        if (empty($coordA) || empty($coordB)) {
            return '';
        }

        $coord = [$coordA[0] - $coordB[0], $coordA[1] - $coordB[1]];

        return PitchInterval::coordToInterval($coord)->name;
    }

    /**
     * Transpose an interval by a number of fifths
     *
     * @param string $interval The interval to transpose
     * @param int $fifths The number of fifths to transpose by
     * @return string The transposed interval name
     *
     * @example
     * Interval::transposeFifths("4P", 1) // => "8P"
     * Interval::transposeFifths("1P", 2) // => "9M"
     */
    public static function transposeFifths(string $interval, int $fifths): string
    {
        $ivl = self::get($interval);

        if ($ivl->empty) {
            return '';
        }

        $coord = $ivl->coord;
        $nFifths = $coord[0];
        $nOcts = $coord[1] ?? 0;
        $dir = $coord[2] ?? 1;

        return PitchInterval::coordToInterval([$nFifths + $fifths, $nOcts, $dir])->name;
    }

    /**
     * Build interval string from properties
     *
     * @param int $step Step (0-6)
     * @param int $alt Alteration
     * @param int $oct Octave
     * @param int $dir Direction (1 or -1)
     * @return string The interval name
     */
    private static function buildFromProps(int $step, int $alt, int $oct, int $dir): string
    {
        $num = $step + 1 + 7 * $oct;
        $d = $dir < 0 ? '-' : '';
        $type = in_array($step, [0, 3, 4], true) ? IntervalType::Perfectable : IntervalType::Majorable;
        $q = self::altToQ($type, $alt);

        return $d . $num . $q;
    }

    /**
     * Convert alteration to quality
     */
    private static function altToQ(IntervalType $type, int $alt): string
    {
        if ($alt === 0) {
            return $type === IntervalType::Majorable ? 'M' : 'P';
        }

        if ($alt === -1 && $type === IntervalType::Majorable) {
            return 'm';
        }

        if ($alt > 0) {
            return str_repeat('A', $alt);
        }

        return str_repeat('d', $type === IntervalType::Perfectable ? abs($alt) : abs($alt) - 1);
    }
}
