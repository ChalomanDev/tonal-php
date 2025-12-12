<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

use Chaloman\Tonal\Data\VoicingDictionaryData;

/**
 * VoicingDictionary - Look up voicing patterns for chord symbols.
 *
 * Equivalent to @tonaljs/voicing-dictionary
 */
final class VoicingDictionary
{
    /**
     * Get the triads dictionary.
     *
     * @return array<string, array<string>>
     */
    public static function triads(): array
    {
        return VoicingDictionaryData::TRIADS;
    }

    /**
     * Get the lefthand dictionary.
     *
     * @return array<string, array<string>>
     */
    public static function lefthand(): array
    {
        return VoicingDictionaryData::LEFTHAND;
    }

    /**
     * Get the combined (all) dictionary.
     *
     * @return array<string, array<string>>
     */
    public static function all(): array
    {
        return VoicingDictionaryData::ALL;
    }

    /**
     * Get the default dictionary (lefthand).
     *
     * @return array<string, array<string>>
     */
    public static function defaultDictionary(): array
    {
        return self::lefthand();
    }

    /**
     * Look up voicing patterns for a chord symbol in a dictionary.
     *
     * @param string $symbol The chord symbol to look up
     * @param array<string, array<string>>|null $dictionary The dictionary to search in
     * @return array<string>|null The voicing patterns or null if not found
     */
    public static function lookup(string $symbol, ?array $dictionary = null): ?array
    {
        $dictionary ??= self::defaultDictionary();

        // Direct lookup
        if (isset($dictionary[$symbol])) {
            return $dictionary[$symbol];
        }

        // Try to find via chord aliases
        $chord = Chord::get('C' . $symbol);
        $aliases = $chord->aliases;

        foreach (array_keys($dictionary) as $dictSymbol) {
            if (in_array($dictSymbol, $aliases, true)) {
                return $dictionary[$dictSymbol];
            }
        }

        return null;
    }
}
