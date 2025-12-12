<?php

declare(strict_types=1);

namespace Chaloman\Tonal\Extensions\Solfege;

use Chaloman\Tonal\Note;
use Chaloman\Tonal\PitchNote;

/**
 * Bidirectional translator between English and solfege notation.
 *
 * This class is a static utility (Helper), not a domain object.
 * All methods return core types or strings, never custom types.
 */
final class Solfege
{
    private const array TO_ENGLISH = [
        'Do' => 'C', 'Re' => 'D', 'Mi' => 'E', 'Fa' => 'F',
        'Sol' => 'G', 'La' => 'A', 'Si' => 'B', 'Ti' => 'B',
    ];

    private const array TO_SOLFEGE = [
        'C' => 'Do', 'D' => 'Re', 'E' => 'Mi', 'F' => 'Fa',
        'G' => 'Sol', 'A' => 'La', 'B' => 'Si',
    ];

    // Regex with lookahead to avoid false positives
    // "regular" does not become "Dgular"
    // "Dominio" is not converted (no valid musical context after)
    private const string SOLFEGE_PATTERN = '/^(Do|Re|Mi|Fa|Sol|La|Si|Ti)(?=[#b]?\d|[#b]?$|[#b]?\s)/i';

    /**
     * Convert solfege notation to English (canonical).
     *
     * @example Solfege::toEnglish("Do#4")   // "C#4"
     * @example Solfege::toEnglish("Solb3")  // "Gb3"
     * @example Solfege::toEnglish("C4")     // "C4" (unchanged)
     * @example Solfege::toEnglish("regular") // "regular" (unchanged, not a note)
     */
    public static function toEnglish(string $input): string
    {
        // Normalize unicode symbols
        $input = str_replace(['♯', '♭'], ['#', 'b'], $input);

        // First check if it's solfege (takes priority over English
        // because Do/Fa start with D/F which are English notes)
        if (preg_match(self::SOLFEGE_PATTERN, $input)) {
            return preg_replace_callback(
                self::SOLFEGE_PATTERN,
                fn ($m) => self::TO_ENGLISH[ucfirst(strtolower($m[1]))] ?? $m[0],
                $input,
            ) ?? $input;
        }

        // If it's a valid English note, normalize capitalization
        // Pattern: letter A-G, optionally accidentals (#/b/x), optionally octave
        if (preg_match('/^[A-Ga-g][#bx]*-?\d*$/', $input)) {
            return ucfirst($input);
        }

        return $input;
    }

    /**
     * Convert English notation to solfege.
     *
     * @example Solfege::toSolfege("C#4")  // "Do#4"
     * @example Solfege::toSolfege("Gb3")  // "Solb3"
     * @example Solfege::toSolfege("Do4")  // "Do4" (already solfege)
     */
    public static function toSolfege(string $input): string
    {
        // Normalize unicode symbols
        $input = str_replace(['♯', '♭'], ['#', 'b'], $input);

        // If already in solfege, return as-is
        if (preg_match(self::SOLFEGE_PATTERN, $input)) {
            return $input;
        }

        // Convert English to solfege (only if starts with English note letter)
        return preg_replace_callback(
            '/^([A-G])/i',
            fn ($m) => self::TO_SOLFEGE[strtoupper($m[1])] ?? $m[0],
            $input,
        ) ?? $input;
    }

    /**
     * Detect if a string uses solfege notation.
     *
     * @example Solfege::isSolfege("Do4")  // true
     * @example Solfege::isSolfege("C4")   // false
     * @example Solfege::isSolfege("regular") // false
     */
    public static function isSolfege(string $input): bool
    {
        return (bool) preg_match(self::SOLFEGE_PATTERN, $input);
    }

    /**
     * Factory: Create a core Note from solfege notation.
     *
     * IMPORTANT: Returns core Note, not a custom type.
     * This guarantees compatibility with all core functions.
     *
     * @example Solfege::note("Do#4")->midi  // 61
     * @example Solfege::note("Sol4")->freq  // 392.0
     */
    public static function note(string $input): PitchNote
    {
        return Note::get(self::toEnglish($input));
    }

    /**
     * Get the solfege name of a core note.
     *
     * @example Solfege::name(Note::get("C#4"))  // "Do#4"
     */
    public static function name(PitchNote $note): string
    {
        return self::toSolfege($note->name);
    }
}
