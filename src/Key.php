<?php

declare(strict_types=1);

namespace Chaloman\Tonal;

/**
 * Key scale representation
 */
final class KeyScale
{
    public function __construct(
        public readonly string $tonic,
        /** @var array<string> */
        public readonly array $grades,
        /** @var array<string> */
        public readonly array $intervals,
        /** @var array<string> */
        public readonly array $scale,
        /** @var array<string> */
        public readonly array $triads,
        /** @var array<string> */
        public readonly array $chords,
        /** @var array<string> */
        public readonly array $chordsHarmonicFunction,
        /** @var array<string> */
        public readonly array $chordScales,
        /** @var array<string> */
        public readonly array $secondaryDominants,
        /** @var array<string> */
        public readonly array $secondaryDominantSupertonics,
        /** @var array<string> */
        public readonly array $substituteDominants,
        /** @var array<string> */
        public readonly array $substituteDominantSupertonics,
    ) {
    }

    /**
     * @deprecated use secondaryDominantSupertonics
     * @return array<string>
     */
    public function secondaryDominantsMinorRelative(): array
    {
        return $this->secondaryDominantSupertonics;
    }

    /**
     * @deprecated use substituteDominantSupertonics
     * @return array<string>
     */
    public function substituteDominantsMinorRelative(): array
    {
        return $this->substituteDominantSupertonics;
    }
}

/**
 * Major key representation
 */
final class MajorKey
{
    public function __construct(
        public readonly string $type,
        public readonly string $tonic,
        public readonly int $alteration,
        public readonly string $keySignature,
        public readonly string $minorRelative,
        /** @var array<string> */
        public readonly array $grades,
        /** @var array<string> */
        public readonly array $intervals,
        /** @var array<string> */
        public readonly array $scale,
        /** @var array<string> */
        public readonly array $triads,
        /** @var array<string> */
        public readonly array $chords,
        /** @var array<string> */
        public readonly array $chordsHarmonicFunction,
        /** @var array<string> */
        public readonly array $chordScales,
        /** @var array<string> */
        public readonly array $secondaryDominants,
        /** @var array<string> */
        public readonly array $secondaryDominantSupertonics,
        /** @var array<string> */
        public readonly array $substituteDominants,
        /** @var array<string> */
        public readonly array $substituteDominantSupertonics,
    ) {
    }

    /**
     * @deprecated use secondaryDominantSupertonics
     * @return array<string>
     */
    public function secondaryDominantsMinorRelative(): array
    {
        return $this->secondaryDominantSupertonics;
    }

    /**
     * @deprecated use substituteDominantSupertonics
     * @return array<string>
     */
    public function substituteDominantsMinorRelative(): array
    {
        return $this->substituteDominantSupertonics;
    }
}

/**
 * Minor key representation
 */
final class MinorKey
{
    public function __construct(
        public readonly string $type,
        public readonly string $tonic,
        public readonly int $alteration,
        public readonly string $keySignature,
        public readonly string $relativeMajor,
        public readonly KeyScale $natural,
        public readonly KeyScale $harmonic,
        public readonly KeyScale $melodic,
    ) {
    }
}

/**
 * Key chord with roles
 */
final class KeyChord
{
    /**
     * @param array<string> $roles
     */
    public function __construct(
        public readonly string $name,
        public array $roles = [],
    ) {
    }
}

/**
 * Musical keys (major and minor)
 *
 * @see https://github.com/tonaljs/tonal/tree/main/packages/key
 */
final class Key
{
    /**
     * Get a major key properties in a given tonic
     *
     * @param string $tonic The tonic note
     */
    public static function majorKey(string $tonic): MajorKey
    {
        $pc = PitchNote::note($tonic)->pc;

        if ($pc === '') {
            return self::emptyMajorKey();
        }

        $keyScale = self::majorScale($pc);
        $alteration = self::distInFifths('C', $pc);

        return new MajorKey(
            type: 'major',
            tonic: $pc,
            alteration: $alteration,
            keySignature: PitchNote::altToAcc($alteration),
            minorRelative: Note::transpose($pc, '-3m'),
            grades: $keyScale->grades,
            intervals: $keyScale->intervals,
            scale: $keyScale->scale,
            triads: $keyScale->triads,
            chords: $keyScale->chords,
            chordsHarmonicFunction: $keyScale->chordsHarmonicFunction,
            chordScales: $keyScale->chordScales,
            secondaryDominants: $keyScale->secondaryDominants,
            secondaryDominantSupertonics: $keyScale->secondaryDominantSupertonics,
            substituteDominants: $keyScale->substituteDominants,
            substituteDominantSupertonics: $keyScale->substituteDominantSupertonics,
        );
    }

    /**
     * Get minor key properties in a given tonic
     *
     * @param string $tonic The tonic note
     */
    public static function minorKey(string $tonic): MinorKey
    {
        $pc = PitchNote::note($tonic)->pc;

        if ($pc === '') {
            return self::emptyMinorKey();
        }

        $alteration = self::distInFifths('C', $pc) - 3;

        return new MinorKey(
            type: 'minor',
            tonic: $pc,
            alteration: $alteration,
            keySignature: PitchNote::altToAcc($alteration),
            relativeMajor: Note::transpose($pc, '3m'),
            natural: self::naturalScale($pc),
            harmonic: self::harmonicScale($pc),
            melodic: self::melodicScale($pc),
        );
    }

    /**
     * Get a list of available chords for a given major key
     *
     * @param string $tonic The tonic note
     * @return array<KeyChord>
     */
    public static function majorKeyChords(string $tonic): array
    {
        $key = self::majorKey($tonic);
        $chords = [];
        self::keyChordsOfMajor($key, $chords);

        return $chords;
    }

    /**
     * Get a list of available chords for a given minor key
     *
     * @param string $tonic The tonic note
     * @return array<KeyChord>
     */
    public static function minorKeyChords(string $tonic): array
    {
        $key = self::minorKey($tonic);
        $chords = [];
        self::keyChordsOfKeyScale($key->natural, $chords);
        self::keyChordsOfKeyScale($key->harmonic, $chords);
        self::keyChordsOfKeyScale($key->melodic, $chords);

        return $chords;
    }

    /**
     * Given a key signature, returns the tonic of the major key
     *
     * @param string|int $sig Key signature (e.g., "###" or 3)
     * @return string|null The tonic note or null if invalid
     *
     * @example
     * Key::majorTonicFromKeySignature("###") // => "A"
     * Key::majorTonicFromKeySignature(3) // => "A"
     */
    public static function majorTonicFromKeySignature(string|int $sig): ?string
    {
        if (is_int($sig)) {
            return Note::transposeFifths('C', $sig);
        }

        if (preg_match('/^b+|#+$/', $sig)) {
            return Note::transposeFifths('C', PitchNote::accToAlt($sig));
        }

        return null;
    }

    /**
     * Calculate distance in fifths between two notes
     */
    private static function distInFifths(string $from, string $to): int
    {
        $f = PitchNote::note($from);
        $t = PitchNote::note($to);

        if ($f->empty || $t->empty) {
            return 0;
        }

        return $t->coord[0] - $f->coord[0];
    }

    /**
     * Create a key scale from grade data
     *
     * @param array<string> $grades
     * @param array<string> $triads
     * @param array<string> $chordTypes
     * @param array<string> $harmonicFunctions
     * @param array<string> $chordScalesData
     */
    private static function createKeyScale(
        string $tonic,
        array $grades,
        array $triads,
        array $chordTypes,
        array $harmonicFunctions,
        array $chordScalesData,
    ): KeyScale {
        $intervals = array_map(
            fn (string $gr) => RomanNumeral::get($gr)->interval,
            $grades,
        );

        $scale = array_map(
            fn (string $interval) => Note::transpose($tonic, $interval),
            $intervals,
        );

        $chords = self::mapScaleToType($scale, $chordTypes);

        $secondaryDominants = array_map(
            function (string $note, int $index) use ($scale, $chords) {
                $transposed = Note::transpose($note, '5P');
                // A secondary dominant is a V chord which:
                // 1. is not diatonic to the key,
                // 2. it must have a diatonic root.
                if (in_array($transposed, $scale, true) && !in_array($transposed . '7', $chords, true)) {
                    return $transposed . '7';
                }

                return '';
            },
            $scale,
            array_keys($scale),
        );

        $secondaryDominantSupertonics = self::supertonics($secondaryDominants, $triads);

        $substituteDominants = array_map(
            function (string $chord) {
                if ($chord === '') {
                    return '';
                }
                $domRoot = substr($chord, 0, -1);
                $subRoot = Note::transpose($domRoot, '5d');

                return $subRoot . '7';
            },
            $secondaryDominants,
        );

        $substituteDominantSupertonics = self::supertonics($substituteDominants, $triads);

        return new KeyScale(
            tonic: $tonic,
            grades: $grades,
            intervals: $intervals,
            scale: $scale,
            triads: self::mapScaleToType($scale, $triads),
            chords: $chords,
            chordsHarmonicFunction: $harmonicFunctions,
            chordScales: self::mapScaleToType($scale, $chordScalesData, ' '),
            secondaryDominants: $secondaryDominants,
            secondaryDominantSupertonics: $secondaryDominantSupertonics,
            substituteDominants: $substituteDominants,
            substituteDominantSupertonics: $substituteDominantSupertonics,
        );
    }

    /**
     * Map scale notes to chord types
     *
     * @param array<string> $scale
     * @param array<string> $types
     * @return array<string>
     */
    private static function mapScaleToType(array $scale, array $types, string $sep = ''): array
    {
        return array_map(
            fn (string $note, int $i) => $note . $sep . ($types[$i] ?? ''),
            $scale,
            array_keys($scale),
        );
    }

    /**
     * Calculate supertonics for dominants
     *
     * @param array<string> $dominants
     * @param array<string> $targetTriads
     * @return array<string>
     */
    private static function supertonics(array $dominants, array $targetTriads): array
    {
        return array_map(
            function (string $chord, int $index) use ($targetTriads) {
                if ($chord === '') {
                    return '';
                }
                $domRoot = substr($chord, 0, -1);
                $minorRoot = Note::transpose($domRoot, '5P');
                $target = $targetTriads[$index] ?? '';
                $isMinor = str_ends_with($target, 'm');

                return $isMinor ? $minorRoot . 'm7' : $minorRoot . 'm7b5';
            },
            $dominants,
            array_keys($dominants),
        );
    }

    /**
     * Major scale data
     */
    private static function majorScale(string $tonic): KeyScale
    {
        return self::createKeyScale(
            $tonic,
            explode(' ', 'I II III IV V VI VII'),
            explode(' ', ' m m   m dim'),
            explode(' ', 'maj7 m7 m7 maj7 7 m7 m7b5'),
            explode(' ', 'T SD T SD D T D'),
            explode(',', 'major,dorian,phrygian,lydian,mixolydian,minor,locrian'),
        );
    }

    /**
     * Natural minor scale data
     */
    private static function naturalScale(string $tonic): KeyScale
    {
        return self::createKeyScale(
            $tonic,
            explode(' ', 'I II bIII IV V bVI bVII'),
            explode(' ', 'm dim  m m  '),
            explode(' ', 'm7 m7b5 maj7 m7 m7 maj7 7'),
            explode(' ', 'T SD T SD D SD SD'),
            explode(',', 'minor,locrian,major,dorian,phrygian,lydian,mixolydian'),
        );
    }

    /**
     * Harmonic minor scale data
     */
    private static function harmonicScale(string $tonic): KeyScale
    {
        return self::createKeyScale(
            $tonic,
            explode(' ', 'I II bIII IV V bVI VII'),
            explode(' ', 'm dim aug m   dim'),
            explode(' ', 'mMaj7 m7b5 +maj7 m7 7 maj7 o7'),
            explode(' ', 'T SD T SD D SD D'),
            explode(',', 'harmonic minor,locrian 6,major augmented,lydian diminished,phrygian dominant,lydian #9,ultralocrian'),
        );
    }

    /**
     * Melodic minor scale data
     */
    private static function melodicScale(string $tonic): KeyScale
    {
        return self::createKeyScale(
            $tonic,
            explode(' ', 'I II bIII IV V VI VII'),
            explode(' ', 'm m aug   dim dim'),
            explode(' ', 'm6 m7 +maj7 7 7 m7b5 m7b5'),
            explode(' ', 'T SD T SD D  '),
            explode(',', 'melodic minor,dorian b2,lydian augmented,lydian dominant,mixolydian b6,locrian #2,altered'),
        );
    }

    /**
     * Extract chords from major key
     *
     * @param array<KeyChord> $chords
     */
    private static function keyChordsOfMajor(MajorKey $key, array &$chords): void
    {
        $updateChord = function (string $name, string $newRole) use (&$chords) {
            if ($name === '') {
                return;
            }
            $keyChord = null;
            foreach ($chords as $chord) {
                if ($chord->name === $name) {
                    $keyChord = $chord;
                    break;
                }
            }
            if ($keyChord === null) {
                $keyChord = new KeyChord($name, []);
                $chords[] = $keyChord;
            }
            if ($newRole !== '' && !in_array($newRole, $keyChord->roles, true)) {
                $keyChord->roles[] = $newRole;
            }
        };

        foreach ($key->chords as $index => $chordName) {
            $updateChord($chordName, $key->chordsHarmonicFunction[$index] ?? '');
        }
        foreach ($key->secondaryDominants as $index => $chordName) {
            $updateChord($chordName, 'V/' . ($key->grades[$index] ?? ''));
        }
        foreach ($key->secondaryDominantSupertonics as $index => $chordName) {
            $updateChord($chordName, 'ii/' . ($key->grades[$index] ?? ''));
        }
        foreach ($key->substituteDominants as $index => $chordName) {
            $updateChord($chordName, 'subV/' . ($key->grades[$index] ?? ''));
        }
        foreach ($key->substituteDominantSupertonics as $index => $chordName) {
            $updateChord($chordName, 'subii/' . ($key->grades[$index] ?? ''));
        }
    }

    /**
     * Extract chords from key scale
     *
     * @param array<KeyChord> $chords
     */
    private static function keyChordsOfKeyScale(KeyScale $key, array &$chords): void
    {
        $updateChord = function (string $name, string $newRole) use (&$chords) {
            if ($name === '') {
                return;
            }
            $keyChord = null;
            foreach ($chords as $chord) {
                if ($chord->name === $name) {
                    $keyChord = $chord;
                    break;
                }
            }
            if ($keyChord === null) {
                $keyChord = new KeyChord($name, []);
                $chords[] = $keyChord;
            }
            if ($newRole !== '' && !in_array($newRole, $keyChord->roles, true)) {
                $keyChord->roles[] = $newRole;
            }
        };

        foreach ($key->chords as $index => $chordName) {
            $updateChord($chordName, $key->chordsHarmonicFunction[$index] ?? '');
        }
        foreach ($key->secondaryDominants as $index => $chordName) {
            $updateChord($chordName, 'V/' . ($key->grades[$index] ?? ''));
        }
        foreach ($key->secondaryDominantSupertonics as $index => $chordName) {
            $updateChord($chordName, 'ii/' . ($key->grades[$index] ?? ''));
        }
        foreach ($key->substituteDominants as $index => $chordName) {
            $updateChord($chordName, 'subV/' . ($key->grades[$index] ?? ''));
        }
        foreach ($key->substituteDominantSupertonics as $index => $chordName) {
            $updateChord($chordName, 'subii/' . ($key->grades[$index] ?? ''));
        }
    }

    /**
     * Create empty major key
     */
    private static function emptyMajorKey(): MajorKey
    {
        return new MajorKey(
            type: 'major',
            tonic: '',
            alteration: 0,
            keySignature: '',
            minorRelative: '',
            grades: [],
            intervals: [],
            scale: [],
            triads: [],
            chords: [],
            chordsHarmonicFunction: [],
            chordScales: [],
            secondaryDominants: [],
            secondaryDominantSupertonics: [],
            substituteDominants: [],
            substituteDominantSupertonics: [],
        );
    }

    /**
     * Create empty minor key
     */
    private static function emptyMinorKey(): MinorKey
    {
        $emptyScale = new KeyScale(
            tonic: '',
            grades: [],
            intervals: [],
            scale: [],
            triads: [],
            chords: [],
            chordsHarmonicFunction: [],
            chordScales: [],
            secondaryDominants: [],
            secondaryDominantSupertonics: [],
            substituteDominants: [],
            substituteDominantSupertonics: [],
        );

        return new MinorKey(
            type: 'minor',
            tonic: '',
            alteration: 0,
            keySignature: '',
            relativeMajor: '',
            natural: $emptyScale,
            harmonic: $emptyScale,
            melodic: $emptyScale,
        );
    }
}
