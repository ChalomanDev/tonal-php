<?php

declare(strict_types=1);

use Chaloman\Tonal\Core;
use Chaloman\Tonal\Pitch;

describe('Core', function () {
    test('fillStr repeats a character', function () {
        expect(Core::fillStr('#', 3))->toBe('###');
        expect(Core::fillStr('b', 2))->toBe('bb');
        expect(Core::fillStr('x', 0))->toBe('');
        expect(Core::fillStr('#', -3))->toBe('###'); // Uses absolute value
    });

    test('transpose delegates to PitchDistance', function () {
        expect(Core::transpose('C4', '3M'))->toBe('E4');
        expect(Core::transpose('D', '5P'))->toBe('A');
    });

    test('distance delegates to PitchDistance', function () {
        expect(Core::distance('C4', 'E4'))->toBe('3M');
        expect(Core::distance('C', 'G'))->toBe('5P');
    });

    test('interval delegates to PitchInterval', function () {
        $ivl = Core::interval('3M');
        expect($ivl->name)->toBe('3M');
        expect($ivl->semitones)->toBe(4);
    });

    test('coordToInterval delegates to PitchInterval', function () {
        $ivl = Core::coordToInterval([4, -2]); // 3M
        expect($ivl->name)->toBe('3M');
    });

    test('note delegates to PitchNote', function () {
        $note = Core::note('C4');
        expect($note->name)->toBe('C4');
        expect($note->midi)->toBe(60);
    });

    test('coordToNote delegates to PitchNote', function () {
        // [0, 0] = C at octave 0 (fifths=0, octaves=0)
        $note = Core::coordToNote([0, 0]);
        expect($note->name)->toBe('C0');

        // [0, 4] = C4
        $note4 = Core::coordToNote([0, 4]);
        expect($note4->name)->toBe('C4');
    });

    test('isPitch checks for Pitch instance', function () {
        $pitch = new Pitch(0, 0, 4);
        expect(Core::isPitch($pitch))->toBeTrue();
        expect(Core::isPitch('C4'))->toBeFalse();
        expect(Core::isPitch(null))->toBeFalse();
    });

    test('chroma calculates pitch chroma', function () {
        $c = new Pitch(0, 0); // C
        expect(Core::chroma($c))->toBe(0);

        $cSharp = new Pitch(0, 1); // C#
        expect(Core::chroma($cSharp))->toBe(1);

        $d = new Pitch(1, 0); // D
        expect(Core::chroma($d))->toBe(2);
    });

    test('height calculates pitch height', function () {
        $c4 = new Pitch(0, 0, 4);
        expect(Core::height($c4))->toBe(48);

        $c5 = new Pitch(0, 0, 5);
        expect(Core::height($c5))->toBe(60);
    });

    test('midi calculates MIDI number', function () {
        $c4 = new Pitch(0, 0, 4);
        expect(Core::midi($c4))->toBe(60);

        // Pitch class (no octave) returns null
        $c = new Pitch(0, 0);
        expect(Core::midi($c))->toBeNull();
    });

    test('coordinates returns pitch coordinates', function () {
        // C4: fifths=0, octaves=4 (relative to internal reference)
        $c4 = new Pitch(0, 0, 4);
        expect(Core::coordinates($c4))->toBe([0, 4]);

        // C pitch class: only fifths coordinate
        $c = new Pitch(0, 0);
        expect(Core::coordinates($c))->toBe([0]);
    });
});
