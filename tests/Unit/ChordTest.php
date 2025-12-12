<?php

declare(strict_types=1);

use Chaloman\Tonal\Chord;

describe('Chord', function () {
    test('tokenize', function () {
        expect(Chord::tokenize('Cmaj7'))->toBe(['C', 'maj7', '']);
        expect(Chord::tokenize('c7'))->toBe(['C', '7', '']);
        expect(Chord::tokenize('maj7'))->toBe(['', 'maj7', '']);
        expect(Chord::tokenize('c#4 m7b5'))->toBe(['C#', '4m7b5', '']);
        expect(Chord::tokenize('c#4m7b5'))->toBe(['C#', '4m7b5', '']);
        expect(Chord::tokenize('Cb7b5'))->toBe(['Cb', '7b5', '']);
        expect(Chord::tokenize('Eb7add6'))->toBe(['Eb', '7add6', '']);
        expect(Chord::tokenize('Bb6b5'))->toBe(['Bb', '6b5', '']);
        expect(Chord::tokenize('aug'))->toBe(['', 'aug', '']);
        expect(Chord::tokenize('C11'))->toBe(['C', '11', '']);
        expect(Chord::tokenize('C13no5'))->toBe(['C', '13no5', '']);
        expect(Chord::tokenize('C64'))->toBe(['C', '64', '']);
        expect(Chord::tokenize('C9'))->toBe(['C', '9', '']);
        expect(Chord::tokenize('C5'))->toBe(['C', '5', '']);
        expect(Chord::tokenize('C4'))->toBe(['C', '4', '']);
        expect(Chord::tokenize("C4|\n"))->toBe(['', "C4|\n", '']);

        // With bass
        expect(Chord::tokenize('Cmaj7/G'))->toBe(['C', 'maj7', 'G']);
        expect(Chord::tokenize('bb6/a##'))->toBe(['Bb', '6', 'A##']);
        expect(Chord::tokenize('bb6/a##5'))->toBe(['Bb', '6/a##5', '']);
    });

    describe('getChord', function () {
        test('Chord properties', function () {
            $chord = Chord::getChord('maj7', 'G4', 'G4');
            expect($chord->empty)->toBeFalse()
                ->and($chord->name)->toBe('G major seventh')
                ->and($chord->symbol)->toBe('Gmaj7')
                ->and($chord->tonic)->toBe('G')
                ->and($chord->root)->toBe('G')
                ->and($chord->bass)->toBe('')
                ->and($chord->rootDegree)->toBe(1)
                ->and($chord->setNum)->toBe(2193)
                ->and($chord->type)->toBe('major seventh')
                ->and($chord->aliases)->toBe(['maj7', 'Δ', 'ma7', 'M7', 'Maj7', '^7'])
                ->and($chord->chroma)->toBe('100010010001')
                ->and($chord->intervals)->toBe(['1P', '3M', '5P', '7M'])
                ->and($chord->normalized)->toBe('100010010001')
                ->and($chord->notes)->toBe(['G', 'B', 'D', 'F#'])
                ->and($chord->quality)->toBe('Major');
        });

        test('first inversion', function () {
            $chord = Chord::getChord('maj7', 'G4', 'B4');
            expect($chord->empty)->toBeFalse()
                ->and($chord->name)->toBe('G major seventh over B')
                ->and($chord->symbol)->toBe('Gmaj7/B')
                ->and($chord->tonic)->toBe('G')
                ->and($chord->root)->toBe('B')
                ->and($chord->bass)->toBe('B')
                ->and($chord->rootDegree)->toBe(2)
                ->and($chord->intervals)->toBe(['3M', '5P', '7M', '8P'])
                ->and($chord->notes)->toBe(['B', 'D', 'F#', 'G']);
        });

        test('first inversion without octave', function () {
            $chord = Chord::getChord('maj7', 'G', 'B');
            expect($chord->name)->toBe('G major seventh over B')
                ->and($chord->symbol)->toBe('Gmaj7/B')
                ->and($chord->rootDegree)->toBe(2)
                ->and($chord->intervals)->toBe(['3M', '5P', '7M', '8P'])
                ->and($chord->notes)->toBe(['B', 'D', 'F#', 'G']);
        });

        test('second inversion', function () {
            $chord = Chord::getChord('maj7', 'G4', 'D5');
            expect($chord->name)->toBe('G major seventh over D')
                ->and($chord->symbol)->toBe('Gmaj7/D')
                ->and($chord->rootDegree)->toBe(3)
                ->and($chord->intervals)->toBe(['5P', '7M', '8P', '10M'])
                ->and($chord->notes)->toBe(['D', 'F#', 'G', 'B']);
        });

        test('without root', function () {
            $chord = Chord::getChord('M7', 'G');
            expect($chord->symbol)->toBe('GM7')
                ->and($chord->name)->toBe('G major seventh')
                ->and($chord->notes)->toBe(['G', 'B', 'D', 'F#']);
        });

        test('rootDegrees', function () {
            expect(Chord::getChord('maj7', 'C', 'C')->rootDegree)->toBe(1)
                ->and(is_nan(Chord::getChord('maj7', 'C', 'D')->rootDegree))->toBeTrue();
        });

        test('without tonic nor root', function () {
            $chord = Chord::getChord('dim');
            expect($chord->symbol)->toBe('dim')
                ->and($chord->name)->toBe('diminished')
                ->and($chord->tonic)->toBeNull()
                ->and($chord->root)->toBe('')
                ->and($chord->bass)->toBe('')
                ->and(is_nan($chord->rootDegree))->toBeTrue()
                ->and($chord->type)->toBe('diminished')
                ->and($chord->aliases)->toBe(['dim', '°', 'o'])
                ->and($chord->chroma)->toBe('100100100000')
                ->and($chord->empty)->toBeFalse()
                ->and($chord->intervals)->toBe(['1P', '3m', '5d'])
                ->and($chord->normalized)->toBe('100000100100')
                ->and($chord->notes)->toBe([])
                ->and($chord->quality)->toBe('Diminished')
                ->and($chord->setNum)->toBe(2336);
        });
    });

    test('chord', function () {
        $chord = Chord::get('Cmaj7');
        expect($chord->empty)->toBeFalse();
        expect($chord->symbol)->toBe('Cmaj7');
        expect($chord->name)->toBe('C major seventh');
        expect($chord->tonic)->toBe('C');
        expect($chord->root)->toBe('');
        expect($chord->bass)->toBe('');
        expect(is_nan($chord->rootDegree))->toBeTrue();
        expect($chord->setNum)->toBe(2193);
        expect($chord->type)->toBe('major seventh');
        expect($chord->aliases)->toBe(['maj7', 'Δ', 'ma7', 'M7', 'Maj7', '^7']);
        expect($chord->chroma)->toBe('100010010001');
        expect($chord->intervals)->toBe(['1P', '3M', '5P', '7M']);
        expect($chord->normalized)->toBe('100010010001');
        expect($chord->notes)->toBe(['C', 'E', 'G', 'B']);
        expect($chord->quality)->toBe('Major');

        expect(Chord::get('hello')->empty)->toBeTrue();
        expect(Chord::get('')->empty)->toBeTrue();
        expect(Chord::get('C')->name)->toBe('C major');

        // Chord with bass, without root
        $cBb = Chord::chord('C/Bb');
        expect($cBb->aliases)->toBe(['M', '^', '', 'maj']);
        expect($cBb->bass)->toBe('Bb');
        expect($cBb->chroma)->toBe('100010010000');
        expect($cBb->empty)->toBeFalse();
        expect($cBb->intervals)->toBe(['-2M', '1P', '3M', '5P']);
        expect($cBb->name)->toBe('C major over Bb');
        expect($cBb->normalized)->toBe('100001000100');
        expect($cBb->notes)->toBe(['Bb', 'C', 'E', 'G']);
        expect($cBb->quality)->toBe('Major');
        expect($cBb->root)->toBe('');
        expect(is_nan($cBb->rootDegree))->toBeTrue();
        expect($cBb->setNum)->toBe(2192);
        expect($cBb->symbol)->toBe('C/Bb');
        expect($cBb->tonic)->toBe('C');
        expect($cBb->type)->toBe('major');
    });

    test('chord without tonic', function () {
        expect(Chord::get('dim')->name)->toBe('diminished')
            ->and(Chord::get('dim7')->name)->toBe('diminished seventh')
            ->and(Chord::get('alt7')->name)->toBe('altered');
    });

    test('notes property', function () {
        expect(Chord::get('Cmaj7')->notes)->toBe(['C', 'E', 'G', 'B'])
            ->and(Chord::get('Eb7add6')->notes)->toBe(['Eb', 'G', 'Bb', 'Db', 'C'])
            ->and(Chord::get(['C4', 'maj7'])->notes)->toBe(['C', 'E', 'G', 'B'])
            ->and(Chord::get('C7')->notes)->toBe(['C', 'E', 'G', 'Bb'])
            ->and(Chord::get('Cmaj7#5')->notes)->toBe(['C', 'E', 'G#', 'B'])
            ->and(Chord::get('blah')->notes)->toBe([]);
    });

    test('notes with two params', function () {
        expect(Chord::get(['C', 'maj7'])->notes)->toBe(['C', 'E', 'G', 'B'])
            ->and(Chord::get(['C6', 'maj7'])->notes)->toBe(['C', 'E', 'G', 'B']);
    });

    test('augmented chords (issue #52)', function () {
        expect(Chord::get('Caug')->notes)->toBe(['C', 'E', 'G#'])
            ->and(Chord::get(['C', 'aug'])->notes)->toBe(['C', 'E', 'G#']);
    });

    test('intervals', function () {
        expect(Chord::get('maj7')->intervals)->toBe(['1P', '3M', '5P', '7M'])
            ->and(Chord::get('Cmaj7')->intervals)->toBe(['1P', '3M', '5P', '7M'])
            ->and(Chord::get('aug')->intervals)->toBe(['1P', '3M', '5A'])
            ->and(Chord::get('C13no5')->intervals)->toBe(['1P', '3M', '7m', '9M', '13M'])
            ->and(Chord::get('major')->intervals)->toBe(['1P', '3M', '5P']);
    });

    test('notes function', function () {
        expect(Chord::notes('Cmaj7'))->toBe(['C', 'E', 'G', 'B'])
            ->and(Chord::notes('maj7'))->toBe([])
            ->and(Chord::notes('maj7', 'C4'))->toBe(['C4', 'E4', 'G4', 'B4'])
            ->and(Chord::notes('Cmaj7', 'C4'))->toBe(['C4', 'E4', 'G4', 'B4'])
            ->and(Chord::notes('Cmaj7', 'D4'))->toBe(['D4', 'F#4', 'A4', 'C#5'])
            ->and(Chord::notes('C/Bb', 'D4'))->toBe(['C4', 'D4', 'F#4', 'A4']);
    });

    test('existence', function () {
        expect(Chord::get('C6add9')->name)->toBe('C sixth added ninth')
            ->and(Chord::get('maj7')->empty)->toBeFalse()
            ->and(Chord::get('Cmaj7')->empty)->toBeFalse()
            ->and(Chord::get('mixolydian')->empty)->toBeTrue();
    });

    test('chordScales', function () {
        $names = 'phrygian dominant,flamenco,spanish heptatonic,half-whole diminished,chromatic';
        expect(Chord::chordScales('C7b9'))->toBe(explode(',', $names));
    });

    test('transpose chord names', function () {
        expect(Chord::transpose('Eb7b9', '5P'))->toBe('Bb7b9')
            ->and(Chord::transpose('7b9', '5P'))->toBe('7b9')
            ->and(Chord::transpose('Cmaj7/B', 'P5'))->toBe('Gmaj7/F#');
    });

    test('extended', function () {
        $chords = 'Cmaj#4 Cmaj7#9#11 Cmaj9 CM7add13 Cmaj13 Cmaj9#11 CM13#11 CM7b9';
        $result = Chord::extended('CMaj7');
        sort($result);
        $expected = explode(' ', $chords);
        sort($expected);
        expect($result)->toBe($expected);
    });

    test('reduced', function () {
        expect(Chord::reduced('CMaj7'))->toBe(['C5', 'CM']);
    });

    describe('Chord.degrees', function () {
        test('ascending', function () {
            expect(array_map(Chord::degrees('C'), [1, 2, 3, 4]))->toBe(['C', 'E', 'G', 'C'])
                ->and(array_map(Chord::degrees('CM', 'C4'), [1, 2, 3, 4]))->toBe(['C4', 'E4', 'G4', 'C5'])
                ->and(array_map(Chord::degrees('Cm6', 'C4'), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]))
                ->toBe(['C4', 'Eb4', 'G4', 'A4', 'C5', 'Eb5', 'G5', 'A5', 'C6', 'Eb6'])
                ->and(array_map(Chord::degrees('C/B'), [1, 2, 3, 4]))->toBe(['B', 'C', 'E', 'G']);
        });

        test('descending', function () {
            expect(array_map(Chord::degrees('C'), [-1, -2, -3]))->toBe(['G', 'E', 'C'])
                ->and(array_map(Chord::degrees('CM', 'C4'), [-1, -2, -3]))->toBe(['G3', 'E3', 'C3']);
        });
    });

    test('Chord.steps', function () {
        expect(array_map(Chord::steps('aug', 'C4'), [-3, -2, -1, 0, 1, 2, 3]))
            ->toBe(['C3', 'E3', 'G#3', 'C4', 'E4', 'G#4', 'C5']);
    });
});
