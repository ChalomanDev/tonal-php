<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Voicing - Generate and search for chord voicings.
 *
 * Equivalent to @tonaljs/voicing
 */
final class Voicing
{
    /**
     * Default range for voicings.
     *
     * @var array{0: string, 1: string}
     */
    private const array DEFAULT_RANGE = ['C3', 'C5'];

    /**
     * Get a voicing for a chord, optionally using voice leading from a previous voicing.
     *
     * @param string $chord The chord symbol (e.g., "Dm7", "C^7")
     * @param array{0: string, 1: string}|null $range The pitch range [low, high]
     * @param array<string, array<string>>|null $dictionary The voicing dictionary
     * @param callable|null $voiceLeading Voice leading function
     * @param array<string>|null $lastVoicing Previous voicing for voice leading
     * @return array<string> The selected voicing
     */
    public static function get(
        string $chord,
        ?array $range = null,
        ?array $dictionary = null,
        ?callable $voiceLeading = null,
        ?array $lastVoicing = null,
    ): array {
        $range ??= self::DEFAULT_RANGE;
        $dictionary ??= VoicingDictionary::all();
        $voiceLeading ??= [VoiceLeading::class, 'topNoteDiff'];

        $voicings = self::search($chord, $range, $dictionary);

        if (empty($voicings)) {
            return [];
        }

        if ($lastVoicing === null || empty($lastVoicing)) {
            // Pick lowest voicing when no previous voicing
            return $voicings[0];
        }

        // Use voice leading to select the best voicing
        return $voiceLeading($voicings, $lastVoicing);
    }

    /**
     * Search for all possible voicings of a chord within a range.
     *
     * @param string $chord The chord symbol (e.g., "Dm7", "C^7")
     * @param array{0: string, 1: string}|null $range The pitch range [low, high]
     * @param array<string, array<string>>|null $dictionary The voicing dictionary
     * @return array<array<string>> All possible voicings
     */
    public static function search(
        string $chord,
        ?array $range = null,
        ?array $dictionary = null,
    ): array {
        $range ??= self::DEFAULT_RANGE;
        $dictionary ??= VoicingDictionary::triads();

        // Tokenize chord to get tonic and symbol
        [$tonic, $symbol] = Chord::tokenize($chord);

        if ($tonic === '') {
            return [];
        }

        // Look up voicing patterns for the symbol
        $sets = VoicingDictionary::lookup($symbol, $dictionary);

        if ($sets === null) {
            return [];
        }

        // Get all chromatic notes in range
        $notesInRange = Range::chromatic($range);

        // Get the top note midi for range limit
        $rangTopMidi = Note::midi($range[1]) ?? 127;

        $result = [];

        foreach ($sets as $intervalString) {
            // Parse intervals from the pattern
            $voicingIntervals = explode(' ', $intervalString);

            // Transpose intervals relative to first interval (e.g., 3m 5P > 1P 3M)
            $firstInterval = $voicingIntervals[0];
            $relativeIntervals = array_map(
                fn (string $interval): string => Interval::subtract($interval, $firstInterval),
                $voicingIntervals,
            );

            // Get enharmonically correct pitch class for the bottom note
            $bottomPitchClass = Note::transpose($tonic, $firstInterval);
            $bottomChroma = Note::chroma($bottomPitchClass);

            // Get the last relative interval for range checking
            $lastRelativeInterval = $relativeIntervals[count($relativeIntervals) - 1];

            // Find all valid start notes
            $starts = [];
            foreach ($notesInRange as $note) {
                // Only consider notes with the same chroma as the bottom pitch class
                if (Note::chroma($note) !== $bottomChroma) {
                    continue;
                }

                // Check if the top note would exceed the range
                $topNote = Note::transpose($note, $lastRelativeInterval);
                $topMidi = Note::midi($topNote) ?? 0;

                if ($topMidi > $rangTopMidi) {
                    continue;
                }

                // Use enharmonic equivalent of the bottom pitch class
                $starts[] = Note::enharmonic($note, $bottomPitchClass);
            }

            // Build voicings from each valid start note
            foreach ($starts as $start) {
                $voicing = array_map(
                    fn (string $interval): string => Note::transpose($start, $interval),
                    $relativeIntervals,
                );
                $result[] = $voicing;
            }
        }

        return $result;
    }

    /**
     * Generate a sequence of voicings for a chord progression using voice leading.
     *
     * @param array<string> $chords Array of chord symbols
     * @param array{0: string, 1: string}|null $range The pitch range [low, high]
     * @param array<string, array<string>>|null $dictionary The voicing dictionary
     * @param callable|null $voiceLeading Voice leading function
     * @param array<string>|null $lastVoicing Initial voicing to lead from
     * @return array<array<string>> Sequence of voicings
     */
    public static function sequence(
        array $chords,
        ?array $range = null,
        ?array $dictionary = null,
        ?callable $voiceLeading = null,
        ?array $lastVoicing = null,
    ): array {
        $range ??= self::DEFAULT_RANGE;
        $dictionary ??= VoicingDictionary::all();
        $voiceLeading ??= [VoiceLeading::class, 'topNoteDiff'];

        $voicings = [];
        $current = $lastVoicing;

        foreach ($chords as $chord) {
            $voicing = self::get($chord, $range, $dictionary, $voiceLeading, $current);
            $voicings[] = $voicing;
            $current = $voicing;
        }

        return $voicings;
    }
}
