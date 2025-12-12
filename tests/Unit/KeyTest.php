<?php

declare(strict_types=1);

use Chaloman\Tonal\Key;
use Chaloman\Tonal\Scale;
use Chaloman\Tonal\Chord;

describe('Key', function () {
    test('majorTonicFromKeySignature', function () {
        expect(Key::majorTonicFromKeySignature('###'))->toBe('A');
        expect(Key::majorTonicFromKeySignature(3))->toBe('A');
        expect(Key::majorTonicFromKeySignature('b'))->toBe('F');
        expect(Key::majorTonicFromKeySignature('bb'))->toBe('Bb');
        expect(Key::majorTonicFromKeySignature('other'))->toBeNull();
    });

    test('major keySignature', function () {
        $tonics = explode(' ', 'C D E F G A B');
        $signatures = array_map(fn($t) => Key::majorKey($t)->keySignature, $tonics);
        expect(implode(' ', $signatures))->toBe(' ## #### b # ### #####');
    });

    test('minor keySignature', function () {
        $tonics = explode(' ', 'C D E F G A B');
        $signatures = array_map(fn($t) => Key::minorKey($t)->keySignature, $tonics);
        expect(implode(' ', $signatures))->toBe('bbb b # bbbb bb  ##');
    });

    describe('scale names', function () {
        test('natural scales', function () {
            $chordScales = Key::minorKey('C')->natural->chordScales;
            $scaleNames = array_map(fn($cs) => Scale::get($cs)->name, $chordScales);
            expect($scaleNames)->toBe([
                'C minor',
                'D locrian',
                'Eb major',
                'F dorian',
                'G phrygian',
                'Ab lydian',
                'Bb mixolydian',
            ]);
        });

        test('harmonic scales', function () {
            $chordScales = Key::minorKey('C')->harmonic->chordScales;
            $scaleNames = array_map(fn($cs) => Scale::get($cs)->name, $chordScales);
            expect($scaleNames)->toBe([
                'C harmonic minor',
                'D locrian 6',
                'Eb major augmented',
                'F lydian diminished',
                'G phrygian dominant',
                'Ab lydian #9',
                'B ultralocrian',
            ]);
        });

        test('melodic scales', function () {
            $chordScales = Key::minorKey('C')->melodic->chordScales;
            $scaleNames = array_map(fn($cs) => Scale::get($cs)->name, $chordScales);
            expect($scaleNames)->toBe([
                'C melodic minor',
                'D dorian b2',
                'Eb lydian augmented',
                'F lydian dominant',
                'G mixolydian b6',
                'A locrian #2',
                'B altered',
            ]);
        });
    });

    test('secondary dominants', function () {
        expect(Key::majorKey('C')->secondaryDominants)->toBe([
            '',
            'A7',
            'B7',
            'C7',
            'D7',
            'E7',
            '',
        ]);
    });

    test('octaves are discarded', function () {
        expect(implode(' ', Key::majorKey('b4')->scale))->toBe('B C# D# E F# G# A#');
        expect(implode(' ', Key::majorKey('g4')->chords))->toBe('Gmaj7 Am7 Bm7 Cmaj7 D7 Em7 F#m7b5');
        expect(implode(' ', Key::minorKey('C4')->melodic->scale))->toBe('C D Eb F G A B');
        expect(implode(' ', Key::minorKey('C4')->melodic->chords))->toBe('Cm6 Dm7 Eb+maj7 F7 G7 Am7b5 Bm7b5');
    });

    test('valid chord names', function () {
        $major = Key::majorKey('C');
        $minor = Key::minorKey('C');

        $allChordSets = [
            $major->chords,
            $major->secondaryDominants,
            $major->secondaryDominantSupertonics,
            $major->substituteDominants,
            $major->substituteDominantsMinorRelative(),
            $minor->natural->chords,
            $minor->harmonic->chords,
            $minor->melodic->chords,
        ];

        foreach ($allChordSets as $chords) {
            foreach ($chords as $name) {
                if ($name !== '') {
                    expect(Chord::get($name)->name)->not->toBe('');
                }
            }
        }
    });

    test('C major key', function () {
        $key = Key::majorKey('C');
        expect($key->type)->toBe('major');
        expect($key->tonic)->toBe('C');
        expect($key->alteration)->toBe(0);
        expect($key->keySignature)->toBe('');
        expect($key->minorRelative)->toBe('A');
        expect($key->scale)->toBe(['C', 'D', 'E', 'F', 'G', 'A', 'B']);
        expect($key->grades)->toBe(['I', 'II', 'III', 'IV', 'V', 'VI', 'VII']);
        expect($key->intervals)->toBe(['1P', '2M', '3M', '4P', '5P', '6M', '7M']);
        expect($key->chords)->toBe(['Cmaj7', 'Dm7', 'Em7', 'Fmaj7', 'G7', 'Am7', 'Bm7b5']);
    });

    test('C major chords', function () {
        $chords = Key::majorKeyChords('C');
        $em7 = null;
        foreach ($chords as $chord) {
            if ($chord->name === 'Em7') {
                $em7 = $chord;
                break;
            }
        }
        expect($em7)->not->toBeNull();
        expect($em7->name)->toBe('Em7');
        expect($em7->roles)->toBe(['T', 'ii/II']);
    });

    test('empty major key', function () {
        $key = Key::majorKey('');
        expect($key->type)->toBe('major');
        expect($key->tonic)->toBe('');
        expect($key->scale)->toBe([]);
    });

    test('C minor key', function () {
        $key = Key::minorKey('C');
        expect($key->type)->toBe('minor');
        expect($key->tonic)->toBe('C');
        expect($key->alteration)->toBe(-3);
        expect($key->keySignature)->toBe('bbb');
        expect($key->relativeMajor)->toBe('Eb');

        // Natural
        expect($key->natural->scale)->toBe(['C', 'D', 'Eb', 'F', 'G', 'Ab', 'Bb']);
        expect($key->natural->chords)->toBe(['Cm7', 'Dm7b5', 'Ebmaj7', 'Fm7', 'Gm7', 'Abmaj7', 'Bb7']);

        // Harmonic
        expect($key->harmonic->scale)->toBe(['C', 'D', 'Eb', 'F', 'G', 'Ab', 'B']);
        expect($key->harmonic->chords)->toBe(['CmMaj7', 'Dm7b5', 'Eb+maj7', 'Fm7', 'G7', 'Abmaj7', 'Bo7']);

        // Melodic
        expect($key->melodic->scale)->toBe(['C', 'D', 'Eb', 'F', 'G', 'A', 'B']);
        expect($key->melodic->chords)->toBe(['Cm6', 'Dm7', 'Eb+maj7', 'F7', 'G7', 'Am7b5', 'Bm7b5']);
    });

    test('empty minor key', function () {
        $key = Key::minorKey('nothing');
        expect($key->type)->toBe('minor');
        expect($key->tonic)->toBe('');
        expect($key->natural->scale)->toBe([]);
    });

    test('A major key', function () {
        $key = Key::majorKey('A');
        expect($key->alteration)->toBe(3);
        expect($key->keySignature)->toBe('###');
        expect($key->minorRelative)->toBe('F#');
        expect($key->scale)->toBe(['A', 'B', 'C#', 'D', 'E', 'F#', 'G#']);
    });

    test('Bb major key', function () {
        $key = Key::majorKey('Bb');
        expect($key->alteration)->toBe(-2);
        expect($key->keySignature)->toBe('bb');
        expect($key->scale)->toBe(['Bb', 'C', 'D', 'Eb', 'F', 'G', 'A']);
    });

    test('E major key', function () {
        $key = Key::majorKey('E');
        expect($key->alteration)->toBe(4);
        expect($key->keySignature)->toBe('####');
        expect($key->scale)->toBe(['E', 'F#', 'G#', 'A', 'B', 'C#', 'D#']);
    });
});
