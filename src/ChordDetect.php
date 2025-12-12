<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Chord detection from notes
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/chord-detect
 */
final class ChordDetect
{
    /**
     * Bitmask for any third (minor or major)
     * 3m = bit 3 (000100000000 = 256)
     * 3M = bit 4 (000010000000 = 128)
     */
    private const int BITMASK_ANY_THIRDS = 384;

    /**
     * Bitmask for perfect fifth (bit 7 = 000000010000 = 16)
     */
    private const int BITMASK_PERFECT_FIFTH = 16;

    /**
     * Bitmask for non-perfect fifths
     * 5d = bit 6 (000000100000 = 32)
     * 5A = bit 8 (000000001000 = 8)
     */
    private const int BITMASK_NON_PERFECT_FIFTHS = 40;

    /**
     * Bitmask for any seventh (minor or major)
     * 7m = bit 10 (000000000010 = 2)
     * 7M = bit 11 (000000000001 = 1)
     */
    private const int BITMASK_ANY_SEVENTH = 3;

    /**
     * Detect chords from a list of notes
     *
     * @param array<string> $source The notes to detect chords from
     * @param array{assumePerfectFifth?: bool} $options Detection options
     * @return array<string> The detected chord names, sorted by weight
     *
     * @example
     * ChordDetect::detect(['D', 'F#', 'A', 'C']) // => ['D7']
     * ChordDetect::detect(['F#', 'A', 'C', 'D']) // => ['D7/F#']
     */
    public static function detect(array $source, array $options = []): array
    {
        // Get pitch classes from notes
        $notes = array_values(array_filter(
            array_map(fn(string $n) => PitchNote::note($n)->pc, $source)
        ));

        if (empty($notes)) {
            return [];
        }

        $found = self::findMatches($notes, 1.0, $options);

        // Filter, sort by weight descending, and extract names
        $filtered = array_filter($found, fn(array $chord) => $chord['weight'] > 0);
        usort($filtered, fn(array $a, array $b) => $b['weight'] <=> $a['weight']);

        return array_map(fn(array $chord) => $chord['name'], $filtered);
    }

    /**
     * Find matching chords
     *
     * @param array<string> $notes The pitch class names
     * @param float $weight Base weight for matches
     * @param array{assumePerfectFifth?: bool} $options Detection options
     * @return array<array{weight: float, name: string}>
     */
    private static function findMatches(array $notes, float $weight, array $options): array
    {
        $tonic = $notes[0];
        $tonicChroma = PitchNote::note($tonic)->chroma;
        $noteName = self::namedSet($notes);

        // Get all modes/rotations of the set
        $allModes = Pcset::modes($notes, false);

        $found = [];

        foreach ($allModes as $index => $mode) {
            $modeWithPerfectFifth = ($options['assumePerfectFifth'] ?? false)
                ? self::withPerfectFifth($mode)
                : null;

            // Find chord types matching this mode
            $chordTypes = array_filter(
                ChordType::all(),
                function (ChordType $chordType) use ($options, $mode, $modeWithPerfectFifth) {
                    if (($options['assumePerfectFifth'] ?? false)
                        && self::hasAnyThirdAndPerfectFifthAndAnySeventh($chordType)
                    ) {
                        return $chordType->chroma === $modeWithPerfectFifth;
                    }

                    return $chordType->chroma === $mode;
                }
            );

            foreach ($chordTypes as $chordType) {
                $chordName = $chordType->aliases[0] ?? '';
                $baseNote = $noteName($index);

                if ($baseNote === null) {
                    continue;
                }

                $isInversion = $index !== $tonicChroma;

                if ($isInversion) {
                    $found[] = [
                        'weight' => 0.5 * $weight,
                        'name' => "{$baseNote}{$chordName}/{$tonic}",
                    ];
                } else {
                    $found[] = [
                        'weight' => 1.0 * $weight,
                        'name' => "{$baseNote}{$chordName}",
                    ];
                }
            }
        }

        return $found;
    }

    /**
     * Create a function that maps chroma to note name
     *
     * @param array<string> $notes
     * @return callable(int): ?string
     */
    private static function namedSet(array $notes): callable
    {
        $pcToName = [];

        foreach ($notes as $n) {
            $note = PitchNote::note($n);
            $chroma = $note->chroma;

            if (!$note->empty && !isset($pcToName[$chroma])) {
                $pcToName[$chroma] = $note->name;
            }
        }

        return fn(int $chroma): ?string => $pcToName[$chroma] ?? null;
    }

    /**
     * Check if a chroma number has any third
     */
    private static function hasAnyThird(int $chromaNumber): bool
    {
        return ($chromaNumber & self::BITMASK_ANY_THIRDS) !== 0;
    }

    /**
     * Check if a chroma number has a perfect fifth
     */
    private static function hasPerfectFifth(int $chromaNumber): bool
    {
        return ($chromaNumber & self::BITMASK_PERFECT_FIFTH) !== 0;
    }

    /**
     * Check if a chroma number has any seventh
     */
    private static function hasAnySeventh(int $chromaNumber): bool
    {
        return ($chromaNumber & self::BITMASK_ANY_SEVENTH) !== 0;
    }

    /**
     * Check if a chroma number has a non-perfect fifth
     */
    private static function hasNonPerfectFifth(int $chromaNumber): bool
    {
        return ($chromaNumber & self::BITMASK_NON_PERFECT_FIFTHS) !== 0;
    }

    /**
     * Check if chord type has any third, perfect fifth, and any seventh
     */
    private static function hasAnyThirdAndPerfectFifthAndAnySeventh(ChordType $chordType): bool
    {
        $chromaNumber = (int) bindec($chordType->chroma);

        return self::hasAnyThird($chromaNumber)
            && self::hasPerfectFifth($chromaNumber)
            && self::hasAnySeventh($chromaNumber);
    }

    /**
     * Add perfect fifth to chroma if it doesn't have non-perfect fifths
     */
    private static function withPerfectFifth(string $chroma): string
    {
        $chromaNumber = (int) bindec($chroma);

        if (self::hasNonPerfectFifth($chromaNumber)) {
            return $chroma;
        }

        // Add perfect fifth (set bit 7)
        $newNumber = $chromaNumber | self::BITMASK_PERFECT_FIFTH;

        return str_pad(decbin($newNumber), 12, '0', STR_PAD_LEFT);
    }
}
