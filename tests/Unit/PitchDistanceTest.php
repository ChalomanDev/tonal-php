<?php

declare(strict_types=1);

use Chaloman\Tonal\PitchDistance;

describe('PitchDistance::transpose', function () {
    test('transposes note by interval', function () {
        expect(PitchDistance::transpose('d3', '3M'))->toBe('F#3');
        expect(PitchDistance::transpose('D', '3M'))->toBe('F#');
    });

    test('transposes multiple notes', function () {
        $notes = ['C', 'D', 'E', 'F', 'G'];
        $transposed = array_map(
            fn($pc) => PitchDistance::transpose($pc, '3M'),
            $notes
        );
        expect($transposed)->toBe(['E', 'F#', 'G#', 'A', 'B']);
    });

    test('returns empty string for invalid notes', function () {
        expect(PitchDistance::transpose('invalid', '3M'))->toBe('');
        expect(PitchDistance::transpose('C4', 'invalid'))->toBe('');
    });

    test('transpose with octave', function () {
        expect(PitchDistance::transpose('A4', '3M'))->toBe('C#5');
        expect(PitchDistance::transpose('C4', '8P'))->toBe('C5');
        expect(PitchDistance::transpose('C4', '-8P'))->toBe('C3');
    });
});

describe('PitchDistance::distance', function () {
    test('interval between notes', function () {
        expect(PitchDistance::distance('C3', 'C3'))->toBe('1P');
        expect(PitchDistance::distance('C3', 'e3'))->toBe('3M');
        expect(PitchDistance::distance('C3', 'e4'))->toBe('10M');
        expect(PitchDistance::distance('C3', 'c2'))->toBe('-8P');
        expect(PitchDistance::distance('C3', 'e2'))->toBe('-6m');
    });

    test('unison interval edge case #243', function () {
        expect(PitchDistance::distance('Db4', 'C#5'))->toBe('7A');
        expect(PitchDistance::distance('Db4', 'C#4'))->toBe('-2d');
        expect(PitchDistance::distance('Db', 'C#'))->toBe('7A');
        expect(PitchDistance::distance('C#', 'Db'))->toBe('2d');
    });

    test('adjacent octaves #428', function () {
        expect(PitchDistance::distance('B#4', 'C4'))->toBe('-7A');
        expect(PitchDistance::distance('B#4', 'C6'))->toBe('9d');
        expect(PitchDistance::distance('B#4', 'C5'))->toBe('2d');
        expect(PitchDistance::distance('B##4', 'C#5'))->toBe('2d');
        expect(PitchDistance::distance('B#5', 'C6'))->toBe('2d');
    });

    test('intervals between pitch classes are always ascending', function () {
        expect(PitchDistance::distance('C', 'D'))->toBe('2M');

        // From C
        $fromC = fn($notes) => implode(' ', array_map(
            fn($n) => PitchDistance::distance('C', $n),
            explode(' ', $notes)
        ));
        expect($fromC('c d e f g a b'))->toBe('1P 2M 3M 4P 5P 6M 7M');

        // From G
        $fromG = fn($notes) => implode(' ', array_map(
            fn($n) => PitchDistance::distance('G', $n),
            explode(' ', $notes)
        ));
        expect($fromG('c d e f g a b'))->toBe('4P 5P 6M 7m 1P 2M 3M');
    });

    test('if a note is a pitch class, the distance is between pitch classes', function () {
        expect(PitchDistance::distance('C', 'C2'))->toBe('1P');
        expect(PitchDistance::distance('C2', 'C'))->toBe('1P');
    });

    test('notes must be valid', function () {
        expect(PitchDistance::distance('one', 'two'))->toBe('');
    });
});

describe('PitchDistance::tonicIntervalsTransposer', function () {
    test('creates transposer function', function () {
        $intervals = ['1P', '3M', '5P'];
        $transposer = PitchDistance::tonicIntervalsTransposer($intervals, 'C4');

        expect($transposer(0))->toBe('C4');
        expect($transposer(1))->toBe('E4');
        expect($transposer(2))->toBe('G4');
    });

    test('handles octave wrapping', function () {
        $intervals = ['1P', '3M', '5P'];
        $transposer = PitchDistance::tonicIntervalsTransposer($intervals, 'C4');

        expect($transposer(3))->toBe('C5');
        expect($transposer(4))->toBe('E5');
    });

    test('returns empty string if no tonic', function () {
        $intervals = ['1P', '3M', '5P'];
        $transposer = PitchDistance::tonicIntervalsTransposer($intervals, null);

        expect($transposer(0))->toBe('');
    });
});
