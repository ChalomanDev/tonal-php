<?php

declare(strict_types=1);

use Chaloman\Tonal\RhythmPattern;

// Helper function for sequential random values
function sequential(float $start, float $step): callable
{
    $current = $start;
    return function () use (&$current, $step): float {
        $current += $step;
        return $current;
    };
}

describe('rhythm-pattern', function () {

    test('generates binary rhythms', function () {
        expect(RhythmPattern::binary(13))->toBe([1, 1, 0, 1]);
        expect(RhythmPattern::binary(12, 13))->toBe([1, 1, 0, 0, 1, 1, 0, 1]);
    });

    test('generates hexadecimal patterns', function () {
        expect(RhythmPattern::hex('8f'))->toBe([1, 0, 0, 0, 1, 1, 1, 1]);
    });

    test('generates rhythms from onset spaces', function () {
        expect(RhythmPattern::onsets(1, 2, 2, 1))->toBe([1, 0, 1, 0, 0, 1, 0, 0, 1, 0]);
    });

    test('generates random patterns with correct length', function () {
        expect(RhythmPattern::random(10))->toHaveCount(10);
    });

    test('generates random patterns with custom rnd', function () {
        $rnd = sequential(0.25, 0.1);
        expect(RhythmPattern::random(5, 0.5, $rnd))->toBe([0, 0, 1, 1, 1]);
    });

    test('generates probabilistic patterns', function () {
        $rnd = fn() => 0.5;
        expect(RhythmPattern::probability([0.5, 0.2, 0, 1, 0], $rnd))->toBe([1, 0, 0, 1, 0]);
    });

    test('rotates pattern', function () {
        expect(RhythmPattern::rotate([1, 0, 0, 1], 0))->toBe([1, 0, 0, 1]);
        expect(RhythmPattern::rotate([1, 0, 0, 1], 1))->toBe([1, 1, 0, 0]);
        expect(RhythmPattern::rotate([1, 0, 0, 1], 2))->toBe([0, 1, 1, 0]);
        expect(RhythmPattern::rotate([1, 0, 0, 1], 3))->toBe([0, 0, 1, 1]);
        expect(RhythmPattern::rotate([1, 0, 0, 1], 4))->toBe([1, 0, 0, 1]);
        expect(RhythmPattern::rotate([1, 0, 0, 1], -1))->toBe([0, 0, 1, 1]);
        expect(RhythmPattern::rotate([1, 0, 0, 1], -2))->toBe([0, 1, 1, 0]);
    });

    test('generates euclidian patterns', function () {
        expect(RhythmPattern::euclid(8, 3))->toBe([1, 0, 0, 1, 0, 0, 1, 0]);
    });

    test('rotate handles empty array', function () {
        expect(RhythmPattern::rotate([], 2))->toBe([]);
    });
});
