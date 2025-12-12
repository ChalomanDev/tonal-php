<?php

declare(strict_types=1);

use Chaloman\Tonal\ChordDetect;

describe('ChordDetect', function () {
    test('detect', function () {
        expect(ChordDetect::detect(['D', 'F#', 'A', 'C']))->toBe(['D7'])
            ->and(ChordDetect::detect(['F#', 'A', 'C', 'D']))->toBe(['D7/F#'])
            ->and(ChordDetect::detect(['A', 'C', 'D', 'F#']))->toBe(['D7/A'])
            ->and(ChordDetect::detect(['E', 'G#', 'B', 'C#']))->toBe(['E6', 'C#m7/E']);
    });

    test('assume perfect 5th', function () {
        expect(ChordDetect::detect(['D', 'F', 'C'], ['assumePerfectFifth' => true]))
            ->toBe(['Dm7'])
            ->and(ChordDetect::detect(['D', 'F', 'C'], ['assumePerfectFifth' => false]))
            ->toBe([])
            ->and(ChordDetect::detect(['D', 'F', 'A', 'C'], ['assumePerfectFifth' => true]))
            ->toBe(['Dm7', 'F6/D'])
            ->and(ChordDetect::detect(['D', 'F', 'A', 'C'], ['assumePerfectFifth' => false]))
            ->toBe(['Dm7', 'F6/D'])
            ->and(ChordDetect::detect(['D', 'F', 'Ab', 'C'], ['assumePerfectFifth' => true]))
            ->toBe(['Dm7b5', 'Fm6/D']);

    });

    test('(regression) detect aug', function () {
        expect(ChordDetect::detect(['C', 'E', 'G#']))
            ->toBe(['Caug', 'Eaug/C', 'G#aug/C']);
    });

    test('edge cases', function () {
        expect(ChordDetect::detect([]))->toBe([]);
    });
});
