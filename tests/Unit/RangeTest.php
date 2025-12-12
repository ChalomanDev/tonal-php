<?php

declare(strict_types=1);

use Chaloman\Tonal\Range;

describe('Range', function () {
    describe('numeric', function () {
        test('special cases', function () {
            expect(Range::numeric([]))->toBe([]);
            expect(Range::numeric(['C4']))->toBe([60]);
        });

        test('note in midi numbers', function () {
            expect(Range::numeric([0, 10]))->toBe([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
            expect(Range::numeric([10, 0]))->toBe([10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0]);
            expect(Range::numeric([5, 0]))->toBe([5, 4, 3, 2, 1, 0]);
            expect(Range::numeric([10, 5]))->toBe([10, 9, 8, 7, 6, 5]);
        });

        test('negative numbers are allowed', function () {
            expect(Range::numeric([-5, 5]))->toBe([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5]);
            expect(Range::numeric([5, -5]))->toBe([5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5]);
            expect(Range::numeric([5, -5, 0]))->toBe([
                5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5, -4, -3, -2, -1, 0,
            ]);
            expect(Range::numeric([-5, -10]))->toBe([-5, -6, -7, -8, -9, -10]);
            expect(Range::numeric([-10, -5]))->toBe([-10, -9, -8, -7, -6, -5]);
        });

        test('notes with names', function () {
            $r1 = [60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72];
            expect(Range::numeric(['C4', 'C5']))->toBe($r1);

            $r2 = [72, 71, 70, 69, 68, 67, 66, 65, 64, 63, 62, 61, 60];
            expect(Range::numeric(['C5', 'C4']))->toBe($r2);
        });

        test('multiple notes in a string', function () {
            expect(Range::numeric(['C2', 'F2', 'Bb1', 'C2']))->toBe([
                36, 37, 38, 39, 40, 41, 40, 39, 38, 37, 36, 35, 34, 35, 36,
            ]);
        });
    });

    describe('chromatic', function () {
        test('note names', function () {
            expect(Range::chromatic(['A3', 'A4']))
                ->toBe(explode(' ', 'A3 Bb3 B3 C4 Db4 D4 Eb4 E4 F4 Gb4 G4 Ab4 A4'));

            expect(Range::chromatic(['A4', 'A3']))
                ->toBe(explode(' ', 'A4 Ab4 G4 Gb4 F4 E4 Eb4 D4 Db4 C4 B3 Bb3 A3'));

            expect(Range::chromatic(['C3', 'Eb3', 'A2']))
                ->toBe(explode(' ', 'C3 Db3 D3 Eb3 D3 Db3 C3 B2 Bb2 A2'));
        });

        test('chromatic - use sharps', function () {
            expect(Range::chromatic(['C2', 'C3'], ['sharps' => true]))
                ->toBe(explode(' ', 'C2 C#2 D2 D#2 E2 F2 F#2 G2 G#2 A2 A#2 B2 C3'));

            expect(Range::chromatic(['C2', 'C3'], ['sharps' => true, 'pitchClass' => true]))
                ->toBe(explode(' ', 'C C# D D# E F F# G G# A A# B C'));
        });
    });
});
