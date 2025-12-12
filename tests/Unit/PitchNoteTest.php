<?php

declare(strict_types=1);

use Chaloman\Tonal\Pitch;
use Chaloman\Tonal\PitchNote;

describe('PitchNote', function () {

    test('tokenize', function () {
        expect(PitchNote::tokenizeNote('Cbb5 major'))->toBe(['C', 'bb', '5', 'major'])
            ->and(PitchNote::tokenizeNote('Ax'))->toBe(['A', '##', '', ''])
            ->and(PitchNote::tokenizeNote('CM'))->toBe(['C', '', '', 'M'])
            ->and(PitchNote::tokenizeNote('maj7'))->toBe(['', '', '', 'maj7'])
            ->and(PitchNote::tokenizeNote(''))->toBe(['', '', '', ''])
            ->and(PitchNote::tokenizeNote('bb'))->toBe(['B', 'b', '', ''])
            ->and(PitchNote::tokenizeNote('##'))->toBe(['', '##', '', '']);
    });

    describe('note properties from string', function () {

        test('properties', function () {
            $note = PitchNote::note('A4');

            expect($note->empty)->toBeFalse()
                ->and($note->name)->toBe('A4')
                ->and($note->letter)->toBe('A')
                ->and($note->acc)->toBe('')
                ->and($note->pc)->toBe('A')
                ->and($note->step)->toBe(5)
                ->and($note->alt)->toBe(0)
                ->and($note->oct)->toBe(4)
                ->and($note->coord)->toBe([3, 3])
                ->and($note->height)->toBe(69)
                ->and($note->chroma)->toBe(9)
                ->and($note->midi)->toBe(69)
                ->and($note->freq)->toBe(440.0);
        });

        test('it accepts a Note as param via cache', function () {
            $n1 = PitchNote::note('C4');
            $n2 = PitchNote::note('C4');

            expect($n1)->toBe($n2);
        });

        test('height', function () {
            $height = fn (string $str) => array_map(
                fn ($n) => PitchNote::note($n)->height,
                explode(' ', $str),
            );

            expect($height('C4 D4 E4 F4 G4'))->toBe([60, 62, 64, 65, 67])
                ->and($height('B-2 C-1 D-1'))->toBe([-1, 0, 2])
                ->and($height('F9 G9 A9'))->toBe([125, 127, 129])
                ->and($height('C-4 D-4 E-4 F-4 G-4'))->toBe([-36, -34, -32, -31, -29])
                ->and($height('C D E F G'))->toBe([-1188, -1186, -1184, -1183, -1181]);

            // Enharmonic equivalences
            expect($height('Cb4 Cbb4 Cbbb4 B#4 B##4 B###4'))->toBe($height('B3 Bb3 Bbb3 C5 C#5 C##5'))
                ->and($height('Cb Cbb Cbbb B# B## B###'))->toBe($height('B Bb Bbb C C# C##'));
        });

        test('midi', function () {
            $midi = fn (string $str) => array_map(
                fn ($n) => PitchNote::note($n)->midi,
                explode(' ', $str),
            );

            expect($midi('C4 D4 E4 F4 G4'))->toBe([60, 62, 64, 65, 67])
                ->and($midi('B-2 C-1 D-1'))->toBe([null, 0, 2])
                ->and($midi('F9 G9 A9'))->toBe([125, 127, null])
                ->and($midi('C-4 D-4 E-4 F-4'))->toBe([null, null, null, null])
                ->and($midi('C D E F'))->toBe([null, null, null, null]);
        });

        test('freq', function () {
            expect(PitchNote::note('C4')->freq)->toEqualWithDelta(261.6255653005986, 0.0001)
                ->and(PitchNote::note('B-2')->freq)->toEqualWithDelta(7.716926582126941, 0.0001)
                ->and(PitchNote::note('F9')->freq)->toEqualWithDelta(11175.303405856126, 0.001)
                ->and(PitchNote::note('C-4')->freq)->toEqualWithDelta(1.0219748644554634, 0.0001)
                ->and(PitchNote::note('C')->freq)->toBeNull()
                ->and(PitchNote::note('x')->freq)->toBeNull();
        });
    });

    test('note properties from pitch properties', function () {
        expect(PitchNote::note(new Pitch(step: 1, alt: -1))->name)->toBe('Db')
            ->and(PitchNote::note(new Pitch(step: 2, alt: 1))->name)->toBe('E#')
            ->and(PitchNote::note(new Pitch(step: 2, alt: 1, oct: 4))->name)->toBe('E#4')
            ->and(PitchNote::note(new Pitch(step: 5, alt: 0))->name)->toBe('A')
            ->and(PitchNote::note(new Pitch(step: -1, alt: 0))->name)->toBe('')
            ->and(PitchNote::note(new Pitch(step: 8, alt: 0))->name)->toBe('');
    });
});
