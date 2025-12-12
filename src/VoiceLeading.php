<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * VoiceLeading - Functions that decide which voicing to pick as a follow-up to a previous voicing.
 *
 * Equivalent to @tonaljs/voice-leading
 */
final class VoiceLeading
{
    /**
     * Select the voicing whose top note is closest to the previous voicing's top note.
     *
     * @param array<array<string>> $voicings Array of possible voicings (each voicing is an array of note names)
     * @param array<string> $lastVoicing The previous voicing to compare against
     * @return array<string> The selected voicing
     */
    public static function topNoteDiff(array $voicings, array $lastVoicing): array
    {
        if (empty($lastVoicing) || empty($voicings)) {
            return $voicings[0] ?? [];
        }

        $topNoteMidi = function (array $voicing): int {
            if (empty($voicing)) {
                return 0;
            }
            return Note::midi($voicing[count($voicing) - 1]) ?? 0;
        };

        $lastTopMidi = $topNoteMidi($lastVoicing);

        $diff = function (array $voicing) use ($topNoteMidi, $lastTopMidi): int {
            return abs($lastTopMidi - $topNoteMidi($voicing));
        };

        // Sort voicings by difference and return the one with the smallest difference
        usort($voicings, fn (array $a, array $b): int => $diff($a) - $diff($b));

        return $voicings[0];
    }
}
