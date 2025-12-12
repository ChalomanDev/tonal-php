<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Musical note representation
 */
final class PitchNote
{
    /**
     * Semitone values for each step
     */
    private const array SEMI = [0, 2, 4, 5, 7, 9, 11];

    /**
     * Regex for parsing note names
     */
    private const string REGEX = '/^([a-gA-G]?)(#{1,}|b{1,}|x{1,}|)(-?\d*)\s*(.*)$/';

    /**
     * Cache for parsed notes
     * @var array<string, self>
     */
    private static array $cache = [];

    /**
     * @param array{0: int, 1?: int} $coord Note coordinates
     */
    public function __construct(
        public readonly bool $empty,
        public readonly string $name,
        public readonly string $letter,
        public readonly string $acc,
        public readonly string $pc,
        public readonly int $step,
        public readonly int $alt,
        public readonly ?int $oct,
        public readonly int $chroma,
        public readonly int $height,
        public readonly array $coord,
        public readonly ?int $midi,
        public readonly ?float $freq,
    ) {
    }

    /**
     * Convert step number to letter
     */
    public static function stepToLetter(int $step): string
    {
        $letters = 'CDEFGAB';

        return $step >= 0 && $step < 7 ? $letters[$step] : '';
    }

    /**
     * Convert alteration to accidental string
     */
    public static function altToAcc(int $alt): string
    {
        if ($alt < 0) {
            return str_repeat('b', abs($alt));
        }

        return str_repeat('#', $alt);
    }

    /**
     * Convert accidental string to alteration number
     */
    public static function accToAlt(string $acc): int
    {
        if ($acc === '') {
            return 0;
        }

        return $acc[0] === 'b' ? -strlen($acc) : strlen($acc);
    }

    /**
     * Get note properties from string, Pitch object, or named pitch
     */
    public static function note(string|Pitch $src): self
    {
        if (is_string($src)) {
            if (isset(self::$cache[$src])) {
                return self::$cache[$src];
            }

            $note = self::parse($src);
            self::$cache[$src] = $note;

            return $note;
        }

        // If it's a Pitch object
        return self::note(self::pitchName($src));
    }

    /**
     * Alias for note()
     */
    public static function get(string|Pitch $src): self
    {
        return self::note($src);
    }

    /**
     * Tokenize a note string
     *
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    public static function tokenizeNote(string $str): array
    {
        if (!preg_match(self::REGEX, $str, $m)) {
            return ['', '', '', ''];
        }

        return [
            strtoupper($m[1]),
            str_replace('x', '##', $m[2]),
            $m[3],
            $m[4],
        ];
    }

    /**
     * Convert coordinates to note
     *
     * @param array{0: int, 1?: int} $noteCoord
     */
    public static function coordToNote(array $noteCoord): self
    {
        $pitch = Pitch::fromCoordinates($noteCoord);

        return self::note($pitch);
    }

    /**
     * Parse a note name string
     */
    private static function parse(string $noteName): self
    {
        $tokens = self::tokenizeNote($noteName);

        if ($tokens[0] === '' || $tokens[3] !== '') {
            return self::empty();
        }

        $letter = $tokens[0];
        $acc = $tokens[1];
        $octStr = $tokens[2];

        $step = (ord($letter) + 3) % 7;
        $alt = self::accToAlt($acc);
        $oct = $octStr !== '' ? (int) $octStr : null;

        $pitch = new Pitch($step, $alt, $oct);
        $coord = Pitch::coordinates($pitch);

        $name = $letter . $acc . $octStr;
        $pc = $letter . $acc;
        $chroma = (self::SEMI[$step] + $alt + 120) % 12;

        $height = $oct === null
            ? self::mod(self::SEMI[$step] + $alt, 12) - 12 * 99
            : self::SEMI[$step] + $alt + 12 * ($oct + 1);

        $midi = ($height >= 0 && $height <= 127) ? $height : null;
        $freq = $oct === null ? null : pow(2, ($height - 69) / 12) * 440;

        return new self(
            empty: false,
            name: $name,
            letter: $letter,
            acc: $acc,
            pc: $pc,
            step: $step,
            alt: $alt,
            oct: $oct,
            chroma: $chroma,
            height: $height,
            coord: $coord,
            midi: $midi,
            freq: $freq,
        );
    }

    /**
     * Create empty note
     */
    private static function empty(): self
    {
        return new self(
            empty: true,
            name: '',
            letter: '',
            acc: '',
            pc: '',
            step: 0,
            alt: 0,
            oct: null,
            chroma: 0,
            height: 0,
            coord: [],
            midi: null,
            freq: null,
        );
    }

    /**
     * Get note name from Pitch
     */
    private static function pitchName(Pitch $props): string
    {
        $letter = self::stepToLetter($props->step);

        if ($letter === '') {
            return '';
        }

        $pc = $letter . self::altToAcc($props->alt);

        if ($props->oct !== null) {
            return $pc . $props->oct;
        }

        return $pc;
    }

    /**
     * Modulo that handles negative numbers
     */
    private static function mod(int $n, int $m): int
    {
        return (($n % $m) + $m) % $m;
    }

    /**
     * Convert to array for testing/serialization
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'empty' => $this->empty,
            'name' => $this->name,
            'letter' => $this->letter,
            'acc' => $this->acc,
            'pc' => $this->pc,
            'step' => $this->step,
            'alt' => $this->alt,
            'oct' => $this->oct,
            'chroma' => $this->chroma,
            'height' => $this->height,
            'coord' => $this->coord,
            'midi' => $this->midi,
            'freq' => $this->freq,
        ];
    }
}
