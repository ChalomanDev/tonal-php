<?php

declare(strict_types=1);

use Chaloman\Tonal\TonalArray;

describe('TonalArray', function () {
    test('range ascending', function () {
        expect(TonalArray::range(-2, 2))->toBe([-2, -1, 0, 1, 2]);
    });

    test('range descending', function () {
        expect(TonalArray::range(2, -2))->toBe([2, 1, 0, -1, -2]);
    });

    test('rotate', function () {
        expect(TonalArray::rotate(2, ['a', 'b', 'c', 'd', 'e']))
            ->toBe(['c', 'd', 'e', 'a', 'b']);
    });

    test('compact', function () {
        $input = ['a', 1, 0, true, false, null, ''];
        $result = TonalArray::compact($input);

        expect($result)->toBe(['a', 1, 0, true]);
    });

    test('sortedNoteNames sorts by height', function () {
        expect(TonalArray::sortedNoteNames(['c2', 'c5', 'c1', 'c0', 'c6', 'c']))
            ->toBe(['C', 'C0', 'C1', 'C2', 'C5', 'C6']);
    });

    test('sortedNoteNames removes invalid notes', function () {
        expect(TonalArray::sortedNoteNames(['c', 'F', 'G', 'a', 'b', 'h', 'J']))
            ->toBe(['C', 'F', 'G', 'A', 'B']);
    });

    test('sortedNoteNames with duplicates', function () {
        expect(TonalArray::sortedNoteNames(['c', 'f', 'g', 'a', 'b', 'h', 'j', 'j', 'h', 'b', 'a', 'g', 'f', 'c']))
            ->toBe(['C', 'C', 'F', 'F', 'G', 'G', 'A', 'A', 'B', 'B']);
    });

    test('sortedUniqNoteNames removes duplicates', function () {
        expect(TonalArray::sortedUniqNoteNames(['a', 'b', 'c2', '1p', 'p2', 'c2', 'b', 'c', 'c3']))
            ->toBe(['C', 'A', 'B', 'C2', 'C3']);
    });

    test('shuffle with deterministic random', function () {
        $rnd = fn () => 0.2;
        expect(TonalArray::shuffle(['a', 'b', 'c', 'd'], $rnd))
            ->toBe(['b', 'c', 'd', 'a']);
    });

    test('permutations', function () {
        expect(TonalArray::permutations(['a', 'b', 'c']))->toBe([
            ['a', 'b', 'c'],
            ['b', 'a', 'c'],
            ['b', 'c', 'a'],
            ['a', 'c', 'b'],
            ['c', 'a', 'b'],
            ['c', 'b', 'a'],
        ]);
    });
});
