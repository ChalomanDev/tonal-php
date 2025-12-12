<?php

declare(strict_types=1);

use Chaloman\Tonal\Collection;

// Helper function similar to JS $ function
function split(string $str): array
{
    return explode(' ', $str);
}

describe('@tonaljs/collection', function () {

    test('range ascending', function () {
        expect(Collection::range(-2, 2))->toBe([-2, -1, 0, 1, 2]);
    });

    test('range descending', function () {
        expect(Collection::range(2, -2))->toBe([2, 1, 0, -1, -2]);
    });

    test('rotate', function () {
        expect(Collection::rotate(2, split('a b c d e')))->toBe(split('c d e a b'));
    });

    test('rotate handles empty array', function () {
        expect(Collection::rotate(2, []))->toBe([]);
    });

    test('rotate handles negative rotation', function () {
        expect(Collection::rotate(-1, split('a b c')))->toBe(split('c a b'));
    });

    test('compact', function () {
        $input = ['a', 1, 0, true, false, null];
        $result = ['a', 1, 0, true];
        expect(Collection::compact($input))->toBe($result);
    });

    test('shuffle with deterministic random', function () {
        $rnd = fn () => 0.2;
        expect(Collection::shuffle(split('a b c d'), $rnd))->toBe(['b', 'c', 'd', 'a']);
    });

    test('permutations', function () {
        expect(Collection::permutations(['a', 'b', 'c']))->toBe([
            ['a', 'b', 'c'],
            ['b', 'a', 'c'],
            ['b', 'c', 'a'],
            ['a', 'c', 'b'],
            ['c', 'a', 'b'],
            ['c', 'b', 'a'],
        ]);
    });

    test('permutations of empty array', function () {
        expect(Collection::permutations([]))->toBe([[]]);
    });

    test('permutations of single element', function () {
        expect(Collection::permutations(['a']))->toBe([['a']]);
    });
});
