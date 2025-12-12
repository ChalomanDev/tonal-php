<?php

declare(strict_types=1);

use Chaloman\Tonal\Pitch;
use Chaloman\Tonal\PitchNote;

describe('PitchNote', function () {

    test('tokenize', function () {
        expect(PitchNote::tokenizeNote('Cbb5 major'))->toBe(['C', 'bb', '5', 'major']);
        expect(PitchNote::tokenizeNote('Ax'))->toBe(['A', '##', '', '']);
        expect(PitchNote::tokenizeNote('CM'))->toBe(['C', '', '', 'M']);
        expect(PitchNote::tokenizeNote('maj7'))->toBe(['', '', '', 'maj7']);
        expect(PitchNote::tokenizeNote(''))->toBe(['', '', '', '']);
        expect(PitchNote::tokenizeNote('bb'))->toBe(['B', 'b', '', '']);
        expect(PitchNote::tokenizeNote('##'))->toBe(['', '##', '', '']);
    });

    describe('note properties from string', function () {

        test('properties', function () {
            $note = PitchNote::note('A4');

            expect($note->empty)->toBeFalse();
            expect($note->name)->toBe('A4');
            expect($note->letter)->toBe('A');
            expect($note->acc)->toBe('');
            expect($note->pc)->toBe('A');
            expect($note->step)->toBe(5);
            expect($note->alt)->toBe(0);
            expect($note->oct)->toBe(4);
            expect($note->coord)->toBe([3, 3]);
            expect($note->height)->toBe(69);
            expect($note->chroma)->toBe(9);
            expect($note->midi)->toBe(69);
            expect($note->freq)->toBe(440.0);
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

            expect($height('C4 D4 E4 F4 G4'))->toBe([60, 62, 64, 65, 67]);
            expect($height('B-2 C-1 D-1'))->toBe([-1, 0, 2]);
            expect($height('F9 G9 A9'))->toBe([125, 127, 129]);
            expect($height('C-4 D-4 E-4 F-4 G-4'))->toBe([-36, -34, -32, -31, -29]);
            expect($height('C D E F G'))->toBe([-1188, -1186, -1184, -1183, -1181]);

            // Enharmonic equivalences
            expect($height('Cb4 Cbb4 Cbbb4 B#4 B##4 B###4'))->toBe(
                $height('B3 Bb3 Bbb3 C5 C#5 C##5'),
            );
            expect($height('Cb Cbb Cbbb B# B## B###'))->toBe(
                $height('B Bb Bbb C C# C##'),
            );
        });

        test('midi', function () {
            $midi = fn (string $str) => array_map(
                fn ($n) => PitchNote::note($n)->midi,
                explode(' ', $str),
            );

            expect($midi('C4 D4 E4 F4 G4'))->toBe([60, 62, 64, 65, 67]);
            expect($midi('B-2 C-1 D-1'))->toBe([null, 0, 2]);
            expect($midi('F9 G9 A9'))->toBe([125, 127, null]);
            expect($midi('C-4 D-4 E-4 F-4'))->toBe([null, null, null, null]);
            expect($midi('C D E F'))->toBe([null, null, null, null]);
        });

        test('freq', function () {
            expect(PitchNote::note('C4')->freq)->toEqualWithDelta(261.6255653005986, 0.0001);
            expect(PitchNote::note('B-2')->freq)->toEqualWithDelta(7.716926582126941, 0.0001);
            expect(PitchNote::note('F9')->freq)->toEqualWithDelta(11175.303405856126, 0.001);
            expect(PitchNote::note('C-4')->freq)->toEqualWithDelta(1.0219748644554634, 0.0001);
            expect(PitchNote::note('C')->freq)->toBeNull();
            expect(PitchNote::note('x')->freq)->toBeNull();
        });
    });

    test('note properties from pitch properties', function () {
        expect(PitchNote::note(new Pitch(step: 1, alt: -1))->name)->toBe('Db');
        expect(PitchNote::note(new Pitch(step: 2, alt: 1))->name)->toBe('E#');
        expect(PitchNote::note(new Pitch(step: 2, alt: 1, oct: 4))->name)->toBe('E#4');
        expect(PitchNote::note(new Pitch(step: 5, alt: 0))->name)->toBe('A');
        expect(PitchNote::note(new Pitch(step: -1, alt: 0))->name)->toBe('');
        expect(PitchNote::note(new Pitch(step: 8, alt: 0))->name)->toBe('');
    });
});
