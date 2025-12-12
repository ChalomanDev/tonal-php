<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * ABC Notation utilities
 *
 * ABC notation is a text-based music notation system.
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/abc-notation
 * @see https://abcnotation.com/
 */
final class AbcNotation
{
    /**
     * Regex for parsing ABC notation
     */
    private const string REGEX = '/^(_{1,}|=|\^{1,}|)([abcdefgABCDEFG])([,\']*)$/';

    /**
     * Tokenize an ABC notation string
     *
     * @param string $str ABC notation string
     * @return array{0: string, 1: string, 2: string} [accidental, letter, octave marks]
     *
     * @example
     * AbcNotation::tokenize("C,',") // => ["", "C", ",',"]
     * AbcNotation::tokenize("^c'") // => ["^", "c", "'"]
     */
    public static function tokenize(string $str): array
    {
        if (!preg_match(self::REGEX, $str, $m)) {
            return ['', '', ''];
        }

        return [$m[1], $m[2], $m[3]];
    }

    /**
     * Convert ABC notation to scientific notation
     *
     * @param string $str ABC notation string
     * @return string Scientific notation string
     *
     * @example
     * AbcNotation::abcToScientificNotation("c") // => "C5"
     * AbcNotation::abcToScientificNotation("^C") // => "C#4"
     * AbcNotation::abcToScientificNotation("_B,") // => "Bb3"
     */
    public static function abcToScientificNotation(string $str): string
    {
        [$acc, $letter, $oct] = self::tokenize($str);

        if ($letter === '') {
            return '';
        }

        // Calculate octave from marks
        $o = 4;
        for ($i = 0; $i < strlen($oct); $i++) {
            $o += $oct[$i] === ',' ? -1 : 1;
        }

        // Convert accidentals: _ -> b, ^ -> #, = -> nothing
        $a = '';
        if ($acc !== '' && $acc[0] === '_') {
            $a = str_replace('_', 'b', $acc);
        } elseif ($acc !== '' && $acc[0] === '^') {
            $a = str_replace('^', '#', $acc);
        }

        // Lowercase letters mean one octave higher
        if (ord($letter) > 96) {
            return strtoupper($letter) . $a . ($o + 1);
        }

        return $letter . $a . $o;
    }

    /**
     * Convert scientific notation to ABC notation
     *
     * @param string $str Scientific notation string
     * @return string ABC notation string
     *
     * @example
     * AbcNotation::scientificToAbcNotation("C#4") // => "^C"
     * AbcNotation::scientificToAbcNotation("D5") // => "d"
     * AbcNotation::scientificToAbcNotation("Bb3") // => "_B,"
     */
    public static function scientificToAbcNotation(string $str): string
    {
        $n = PitchNote::note($str);

        if ($n->empty || $n->oct === null) {
            return '';
        }

        $letter = $n->letter;
        $acc = $n->acc;
        $oct = $n->oct;

        // Convert accidentals: b -> _, # -> ^
        $a = '';
        if ($acc !== '' && $acc[0] === 'b') {
            $a = str_replace('b', '_', $acc);
        } elseif ($acc !== '') {
            $a = str_replace('#', '^', $acc);
        }

        // Determine letter case and octave marks
        $l = $oct > 4 ? strtolower($letter) : $letter;

        // Build octave marks
        $o = '';
        if ($oct === 5) {
            $o = '';
        } elseif ($oct > 4) {
            $o = str_repeat("'", $oct - 5);
        } else {
            $o = str_repeat(',', 4 - $oct);
        }

        return $a . $l . $o;
    }

    /**
     * Transpose a note in ABC notation
     *
     * @param string $note ABC notation note
     * @param string $interval Interval to transpose by
     * @return string Transposed note in ABC notation
     *
     * @example
     * AbcNotation::transpose("=C", "P19") // => "g'"
     */
    public static function transpose(string $note, string $interval): string
    {
        return self::scientificToAbcNotation(
            PitchDistance::transpose(
                self::abcToScientificNotation($note),
                $interval
            )
        );
    }

    /**
     * Calculate the interval distance between two ABC notation notes
     *
     * @param string $from Source note in ABC notation
     * @param string $to Target note in ABC notation
     * @return string Interval name
     *
     * @example
     * AbcNotation::distance("=C", "g") // => "12P"
     */
    public static function distance(string $from, string $to): string
    {
        return PitchDistance::distance(
            self::abcToScientificNotation($from),
            self::abcToScientificNotation($to)
        );
    }
}
