<?php

declare(strict_types=1);

use Chaloman\Tonal\Pcset;

/**
 * Helper function to split string into array
 */
function s(string $str): array
{
    return explode(' ', $str);
}

describe('Pcset', function () {
    describe('get', function () {
        test('from note list', function () {
            expect(Pcset::get(['c', 'd', 'e'])->toArray())->toMatchArray([
                'empty' => false,
                'name' => '',
                'setNum' => 2688,
                'chroma' => '101010000000',
                'normalized' => '100000001010',
                'intervals' => ['1P', '2M', '3M'],
            ]);

            // Order doesn't matter
            expect(Pcset::get(['d', 'e', 'c']))->toEqual(Pcset::get(['c', 'd', 'e']));

            // Invalid notes return empty pcset
            expect(Pcset::get(['not a note or interval'])->empty)->toBeTrue();

            // Empty array returns empty pcset
            expect(Pcset::get([])->empty)->toBeTrue();
        });

        test('from pcset number', function () {
            expect(Pcset::get(2048))->toEqual(Pcset::get(['C']));

            $set = Pcset::get(['D']);
            expect(Pcset::get($set->setNum))->toEqual($set);
        });

        test('normalized', function () {
            $likeC = Pcset::get(['C'])->chroma; // 100000000000
            foreach (str_split('cdefgab') as $pc) {
                expect(Pcset::get([$pc])->normalized)->toBe($likeC);
            }

            expect(Pcset::get(['E', 'F#'])->normalized)->toBe(Pcset::get(['C', 'D'])->normalized);
        });
    });

    test('num', function () {
        expect(Pcset::num('000000000001'))->toBe(1);
        expect(Pcset::num(['B']))->toBe(1);
        expect(Pcset::num(['Cb']))->toBe(1);
        expect(Pcset::num(['C', 'E', 'G']))->toBe(2192);
        expect(Pcset::num(['C']))->toBe(2048);
        expect(Pcset::num('100000000000'))->toBe(2048);
        expect(Pcset::num('111111111111'))->toBe(4095);
    });

    test('chroma', function () {
        expect(Pcset::chroma(['C']))->toBe('100000000000');
        expect(Pcset::chroma(['D']))->toBe('001000000000');
        expect(Pcset::chroma(s('c d e')))->toBe('101010000000');
        expect(Pcset::chroma(s('g g#4 a bb5')))->toBe('000000011110');
        expect(Pcset::chroma(s('P1 M2 M3 P4 P5 M6 M7')))->toBe(Pcset::chroma(s('c d e f g a b')));
        expect(Pcset::chroma('101010101010'))->toBe('101010101010');
        expect(Pcset::chroma(['one', 'two']))->toBe('000000000000');
        // String that's not a valid chroma or array
        expect(Pcset::chroma('A B C'))->toBe('000000000000');
    });

    test('chromas', function () {
        $chromas = Pcset::chromas();
        expect(count($chromas))->toBe(2048);
        expect($chromas[0])->toBe('100000000000');
        expect($chromas[2047])->toBe('111111111111');
    });

    test('intervals', function () {
        expect(Pcset::intervals('101010101010'))->toBe(s('1P 2M 3M 5d 6m 7m'));
        expect(Pcset::intervals('1010'))->toBe([]); // Invalid chroma
        expect(Pcset::intervals(['C', 'G', 'B']))->toBe(['1P', '5P', '7M']);
        expect(Pcset::intervals(['D', 'F', 'A']))->toBe(['2M', '4P', '6M']);
    });

    test('isChroma', function () {
        expect(Pcset::get('101010101010')->chroma)->toBe('101010101010');
        expect(Pcset::get('1010101')->chroma)->toBe('000000000000');
        expect(Pcset::get('blah')->chroma)->toBe('000000000000');
        expect(Pcset::get('c d e')->chroma)->toBe('000000000000');
    });

    test('isSubsetOf', function () {
        $isInCMajor = Pcset::isSubsetOf(s('c4 e6 g'));
        expect($isInCMajor(s('c2 g7')))->toBeTrue();
        expect($isInCMajor(s('c2 e')))->toBeTrue();
        expect($isInCMajor(s('c2 e3 g4')))->toBeFalse(); // Same set, not a proper subset
        expect($isInCMajor(s('c2 e3 b5')))->toBeFalse(); // B not in set

        expect(Pcset::isSubsetOf(s('c d e'))(['C', 'D']))->toBeTrue();
    });

    test('isSubsetOf with chroma', function () {
        $isSubset = Pcset::isSubsetOf('101010101010');
        expect($isSubset('101000000000'))->toBeTrue();
        expect($isSubset('111000000000'))->toBeFalse();
    });

    test('isSupersetOf', function () {
        $extendsCMajor = Pcset::isSupersetOf(['c', 'e', 'g']);
        expect($extendsCMajor(s('c2 g3 e4 f5')))->toBeTrue();
        expect($extendsCMajor(s('e c g')))->toBeFalse(); // Same set, not superset
        expect($extendsCMajor(s('c e f')))->toBeFalse(); // Missing G

        expect(Pcset::isSupersetOf(['c', 'd'])(['c', 'd', 'e']))->toBeTrue();
    });

    test('isSupersetOf with chroma', function () {
        $isSuperset = Pcset::isSupersetOf('101000000000');
        expect($isSuperset('101010101010'))->toBeTrue();
        expect($isSuperset('110010101010'))->toBeFalse();
    });

    test('isEqual', function () {
        expect(Pcset::isEqual(s('c2 d3 e7 f5'), s('c4 c d5 e6 f1')))->toBeTrue();
        expect(Pcset::isEqual(s('c f'), s('c4 c f1')))->toBeTrue();
    });

    test('isNoteIncludedIn', function () {
        $isIncludedInC = Pcset::isNoteIncludedIn(['c', 'd', 'e']);
        expect($isIncludedInC('C4'))->toBeTrue();
        expect($isIncludedInC('C#4'))->toBeFalse();
    });

    test('filter', function () {
        $inCMajor = Pcset::filter(s('c d e'));
        expect($inCMajor(s('c2 c#2 d2 c3 c#3 d3')))->toBe(s('c2 d2 c3 d3'));
        expect(Pcset::filter(s('c'))(s('c2 c#2 d2 c3 c#3 d3')))->toBe(s('c2 c3'));
    });

    test('notes', function () {
        expect(Pcset::notes(s('c d e f g a b')))->toBe(s('C D E F G A B'));
        expect(Pcset::notes(s('b a g f e d c')))->toBe(s('C D E F G A B'));
        expect(Pcset::notes(s('D3 A3 Bb3 C4 D4 E4 F4 G4 A4')))->toBe(s('C D E F G A Bb'));
        expect(Pcset::notes('101011010110'))->toBe(s('C D E F G A Bb'));
        expect(Pcset::notes(['blah', 'x']))->toBe([]);
    });

    test('modes', function () {
        expect(Pcset::modes(s('c d e f g a b')))->toBe([
            '101011010101',
            '101101010110',
            '110101011010',
            '101010110101',
            '101011010110',
            '101101011010',
            '110101101010',
        ]);

        expect(Pcset::modes(s('c d e f g a b'), false))->toBe([
            '101011010101',
            '010110101011',
            '101101010110',
            '011010101101',
            '110101011010',
            '101010110101',
            '010101101011',
            '101011010110',
            '010110101101',
            '101101011010',
            '011010110101',
            '110101101010',
        ]);

        expect(Pcset::modes(['blah', 'bleh']))->toBe([]);
    });
});
