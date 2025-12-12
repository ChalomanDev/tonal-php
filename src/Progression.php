<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Chord progressions
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/progression
 */
final class Progression
{
    /**
     * Given a tonic and a chord list expressed with roman numeral notation
     * returns the progression expressed with leadsheet chord symbols notation
     *
     * @param string $tonic The tonic note
     * @param array<string> $chords Chord list in roman numeral notation
     * @return array<string> Chord list in leadsheet notation
     *
     * @example
     * Progression::fromRomanNumerals("C", ["I", "IIm7", "V7"]) // => ["C", "Dm7", "G7"]
     */
    public static function fromRomanNumerals(string $tonic, array $chords): array
    {
        return array_map(
            function (string $chord) use ($tonic): string {
                $rn = RomanNumeral::get($chord);

                if ($rn->empty) {
                    return '';
                }

                $interval = PitchInterval::interval($rn->interval);
                $transposed = PitchDistance::transpose($tonic, $interval->name);

                return $transposed . $rn->chordType;
            },
            $chords
        );
    }

    /**
     * Given a tonic and a chord list with leadsheet symbols notation,
     * return the chord list with roman numeral notation
     *
     * @param string $tonic The tonic note
     * @param array<string> $chords Chord list in leadsheet notation
     * @return array<string> Chord list in roman numeral notation
     *
     * @example
     * Progression::toRomanNumerals("C", ["CMaj7", "Dm7", "G7"]) // => ["IMaj7", "IIm7", "V7"]
     */
    public static function toRomanNumerals(string $tonic, array $chords): array
    {
        return array_map(
            function (string $chord) use ($tonic): string {
                [$note, $chordType] = Chord::tokenize($chord);

                if ($note === '') {
                    return '';
                }

                $intervalName = PitchDistance::distance($tonic, $note);
                $interval = PitchInterval::interval($intervalName);
                $roman = RomanNumeral::get($interval);

                return $roman->name . $chordType;
            },
            $chords
        );
    }
}
