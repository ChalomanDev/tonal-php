<?php

declare(strict_types=1);

use Chaloman\Tonal\Mode;

describe('Mode', function () {
    describe('mode', function () {
        test('properties', function () {
            $mode = Mode::get('ionian');

            expect($mode->empty)->toBeFalse()
                ->and($mode->modeNum)->toBe(0)
                ->and($mode->name)->toBe('ionian')
                ->and($mode->setNum)->toBe(2773)
                ->and($mode->chroma)->toBe('101011010101')
                ->and($mode->normalized)->toBe('101011010101')
                ->and($mode->alt)->toBe(0)
                ->and($mode->triad)->toBe('')
                ->and($mode->seventh)->toBe('Maj7')
                ->and($mode->aliases)->toBe(['major'])
                ->and($mode->intervals)->toBe(['1P', '2M', '3M', '4P', '5P', '6M', '7M'])
                ->and(Mode::get('major')->name)->toBe(Mode::get('ionian')->name);

        });

        test('accept ModeType as parameter', function () {
            expect(Mode::get(Mode::get('major'))->name)->toBe(Mode::get('major')->name)
                ->and(Mode::get(['name' => 'Major'])->name)->toBe(Mode::get('major')->name);
        });

        test('name is case independent', function () {
            expect(Mode::get('Dorian')->name)->toBe(Mode::get('dorian')->name);
        });

        test('setNum', function () {
            $pcsets = array_map(fn ($name) => Mode::get($name)->setNum, Mode::names());
            expect($pcsets)->toBe([2773, 2902, 3418, 2741, 2774, 2906, 3434]);
        });

        test('alt', function () {
            $alt = array_map(fn ($name) => Mode::get($name)->alt, Mode::names());
            expect($alt)->toBe([0, 2, 4, -1, 1, 3, 5]);
        });

        test('triad', function () {
            $triads = array_map(fn ($name) => Mode::get($name)->triad, Mode::names());
            expect($triads)->toBe(['', 'm', 'm', '', '', 'm', 'dim']);
        });

        test('seventh', function () {
            $sevenths = array_map(fn ($name) => Mode::get($name)->seventh, Mode::names());
            expect($sevenths)->toBe(['Maj7', 'm7', 'm7', 'Maj7', '7', 'm7', 'm7b5']);
        });

        test('aliases', function () {
            expect(Mode::get('major')->name)->toBe(Mode::get('ionian')->name)
                ->and(Mode::get('minor')->name)->toBe(Mode::get('aeolian')->name);
        });
    });

    test('names', function () {
        expect(Mode::names())->toBe([
            'ionian',
            'dorian',
            'phrygian',
            'lydian',
            'mixolydian',
            'aeolian',
            'locrian',
        ]);
    });

    test('notes', function () {
        expect(implode(' ', Mode::notes('major', 'C')))->toBe('C D E F G A B')
            ->and(implode(' ', Mode::notes('dorian', 'C')))->toBe('C D Eb F G A Bb')
            ->and(implode(' ', Mode::notes('dorian', 'F')))->toBe('F G Ab Bb C D Eb')
            ->and(implode(' ', Mode::notes('lydian', 'F')))->toBe('F G A B C D E')
            ->and(implode(' ', Mode::notes('anything', 'F')))->toBe('');
    });

    test('triads', function () {
        expect(implode(' ', Mode::triads('minor', 'C')))->toBe('Cm Ddim Eb Fm Gm Ab Bb')
            ->and(implode(' ', Mode::triads('mixolydian', 'Bb')))->toBe('Bb Cm Ddim Eb Fm Gm Ab');
    });

    test('seventhChords', function () {
        expect(implode(' ', Mode::seventhChords('major', 'C#')))
            ->toBe('C#Maj7 D#m7 E#m7 F#Maj7 G#7 A#m7 B#m7b5')
            ->and(implode(' ', Mode::seventhChords('dorian', 'G')))
            ->toBe('Gm7 Am7 BbMaj7 C7 Dm7 Em7b5 FMaj7');

    });

    test('relativeTonic', function () {
        expect(Mode::relativeTonic('major', 'minor', 'A'))->toBe('C')
            ->and(Mode::relativeTonic('major', 'minor', 'D'))->toBe('F')
            ->and(Mode::relativeTonic('minor', 'dorian', 'D'))->toBe('A')
            ->and(Mode::relativeTonic('nonsense', 'dorian', 'D'))->toBe('');
    });
});
