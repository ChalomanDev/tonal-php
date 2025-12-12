<?php

declare(strict_types=1);

use Chaloman\Tonal\Direction;
use Chaloman\Tonal\Pitch;
use Chaloman\Tonal\PitchInterval;

describe('interval', function () {

    test('tokenize', function () {
        expect(PitchInterval::tokenizeInterval('-2M'))->toBe(['-2', 'M']);
        expect(PitchInterval::tokenizeInterval('M-3'))->toBe(['-3', 'M']);
    });

    describe('interval from string', function () {

        test('has all properties', function () {
            $interval = PitchInterval::interval('4d');

            expect($interval->empty)->toBeFalse();
            expect($interval->name)->toBe('4d');
            expect($interval->num)->toBe(4);
            expect($interval->q)->toBe('d');
            expect($interval->type->value)->toBe('perfectable');
            expect($interval->alt)->toBe(-1);
            expect($interval->chroma)->toBe(4);
            expect($interval->dir)->toBe(1);
            expect($interval->coord)->toBe([-8, 5]);
            expect($interval->oct)->toBe(0);
            expect($interval->semitones)->toBe(4);
            expect($interval->simple)->toBe(4);
            expect($interval->step)->toBe(3);
        });

        test('accepts interval as parameter', function () {
            // For now we test with string since our implementation focuses on strings
            $i1 = PitchInterval::interval('5P');
            $i2 = PitchInterval::interval('5P');

            expect($i1->name)->toBe($i2->name);
        });

        test('name', function () {
            $names = fn(string $src) => implode(' ', array_map(
                fn($s) => PitchInterval::interval($s)->name,
                explode(' ', $src)
            ));

            expect($names('1P 2M 3M 4P 5P 6M 7M'))->toBe('1P 2M 3M 4P 5P 6M 7M');
            expect($names('P1 M2 M3 P4 P5 M6 M7'))->toBe('1P 2M 3M 4P 5P 6M 7M');
            expect($names('-1P -2M -3M -4P -5P -6M -7M'))->toBe('-1P -2M -3M -4P -5P -6M -7M');
            expect($names('P-1 M-2 M-3 P-4 P-5 M-6 M-7'))->toBe('-1P -2M -3M -4P -5P -6M -7M');
            expect(PitchInterval::interval('not-an-interval')->empty)->toBeTrue();
            expect(PitchInterval::interval('2P')->empty)->toBeTrue();
        });

        test('q', function () {
            $q = fn(string $str) => array_map(
                fn($i) => PitchInterval::interval($i)->q,
                explode(' ', $str)
            );

            expect($q('1dd 1d 1P 1A 1AA'))->toBe(['dd', 'd', 'P', 'A', 'AA']);
            expect($q('2dd 2d 2m 2M 2A 2AA'))->toBe(['dd', 'd', 'm', 'M', 'A', 'AA']);
        });

        test('alt', function () {
            $alt = fn(string $str) => array_map(
                fn($i) => PitchInterval::interval($i)->alt,
                explode(' ', $str)
            );

            expect($alt('1dd 2dd 3dd 4dd'))->toBe([-2, -3, -3, -2]);
        });

        test('simple', function () {
            $simple = fn(string $str) => array_map(
                fn($i) => PitchInterval::interval($i)->simple,
                explode(' ', $str)
            );

            expect($simple('1P 2M 3M 4P'))->toBe([1, 2, 3, 4]);
            expect($simple('8P 9M 10M 11P'))->toBe([8, 2, 3, 4]);
            expect($simple('-8P -9M -10M -11P'))->toBe([-8, -2, -3, -4]);
        });
    });

    describe('interval from pitch props', function () {

        test('requires step, alt and dir', function () {
            $pitch1 = new Pitch(step: 0, alt: 0, dir: Direction::Ascending);
            expect(PitchInterval::interval($pitch1)->name)->toBe('1P');

            $pitch2 = new Pitch(step: 0, alt: -2, dir: Direction::Ascending);
            expect(PitchInterval::interval($pitch2)->name)->toBe('1dd');

            $pitch3 = new Pitch(step: 1, alt: 1, dir: Direction::Ascending);
            expect(PitchInterval::interval($pitch3)->name)->toBe('2A');

            $pitch4 = new Pitch(step: 2, alt: -2, dir: Direction::Ascending);
            expect(PitchInterval::interval($pitch4)->name)->toBe('3d');

            $pitch5 = new Pitch(step: 1, alt: 1, dir: Direction::Descending);
            expect(PitchInterval::interval($pitch5)->name)->toBe('-2A');

            // Pitch without dir should return empty
            $pitch6 = new Pitch(step: 1000, alt: 0);
            expect(PitchInterval::interval($pitch6)->empty)->toBeTrue();
        });

        test('accepts octave', function () {
            $p1 = new Pitch(step: 0, alt: 0, oct: 0, dir: Direction::Ascending);
            expect(PitchInterval::interval($p1)->name)->toBe('1P');

            $p2 = new Pitch(step: 0, alt: -1, oct: 1, dir: Direction::Descending);
            expect(PitchInterval::interval($p2)->name)->toBe('-8d');

            $p3 = new Pitch(step: 0, alt: 1, oct: 2, dir: Direction::Descending);
            expect(PitchInterval::interval($p3)->name)->toBe('-15A');

            $p4 = new Pitch(step: 1, alt: -1, oct: 1, dir: Direction::Descending);
            expect(PitchInterval::interval($p4)->name)->toBe('-9m');
        });
    });
});
