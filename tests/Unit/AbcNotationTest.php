<?php

declare(strict_types=1);

use Chaloman\Tonal\AbcNotation;

describe('AbcNotation', function () {
    test('tokenize', function () {
        expect(AbcNotation::tokenize("C,',"))->toBe(['', 'C', ",',"])
            ->and(AbcNotation::tokenize("g,,'"))->toBe(['', 'g', ",,'"])
            ->and(AbcNotation::tokenize(''))->toBe(['', '', ''])
            ->and(AbcNotation::tokenize('m'))->toBe(['', '', ''])
            ->and(AbcNotation::tokenize('c#'))->toBe(['', '', '']);
    });

    test('transpose', function () {
        expect(AbcNotation::transpose('=C', 'P19'))->toBe("g'");
    });

    test('distance', function () {
        expect(AbcNotation::distance('=C', 'g'))->toBe('12P');
    });

    test('toNote', function () {
        $ABC = [
            '__A,,',
            '_B,',
            '=C',
            'd',
            "^e'",
            "^^f''",
            "G,,''",
            "g,,,'''",
            '',
        ];
        $SCIENTIFIC = [
            'Abb2',
            'Bb3',
            'C4',
            'D5',
            'E#6',
            'F##7',
            'G4',
            'G5',
            '',
        ];
        expect(array_map(AbcNotation::abcToScientificNotation(...), $ABC))->toBe($SCIENTIFIC);
    });

    test('toAbc', function () {
        $SCIENTIFIC = [
            'Abb2',
            'Bb3',
            'C4',
            'D5',
            'E#6',
            'F##7',
            'G#2',
            'Gb7',
            '',
        ];
        $ABC = ['__A,,', '_B,', 'C', 'd', "^e'", "^^f''", '^G,,', "_g''", ''];
        expect(array_map(AbcNotation::scientificToAbcNotation(...), $SCIENTIFIC))->toBe($ABC);
    });

    test('toAbc Octave 0', function () {
        $SCIENTIFIC = ['A0', 'Bb0', 'C0', 'D0', 'E#0', 'F##0', 'G#0'];
        $ABC = [
            'A,,,,',
            '_B,,,,',
            'C,,,,',
            'D,,,,',
            '^E,,,,',
            '^^F,,,,',
            '^G,,,,',
        ];
        expect(array_map(AbcNotation::scientificToAbcNotation(...), $SCIENTIFIC))->toBe($ABC);
    });
});
