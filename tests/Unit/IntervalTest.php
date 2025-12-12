<?php

declare(strict_types=1);

use Chaloman\Tonal\Interval;

describe('Interval', function () {
    test('properties', function () {
        $interval = Interval::get('P4');

        expect($interval->alt)->toBe(0)
            ->and($interval->chroma)->toBe(5)
            ->and($interval->coord)->toBe([-1, 1])
            ->and($interval->dir)->toBe(1)
            ->and($interval->empty)->toBeFalse()
            ->and($interval->name)->toBe('4P')
            ->and($interval->num)->toBe(4)
            ->and($interval->oct)->toBe(0)
            ->and($interval->q)->toBe('P')
            ->and($interval->semitones)->toBe(5)
            ->and($interval->simple)->toBe(4)
            ->and($interval->step)->toBe(3)
            ->and($interval->type->value)->toBe('perfectable');
    });

    test('shorthand properties', function () {
        expect(Interval::name('d5'))->toBe('5d');
        expect(Interval::num('d5'))->toBe(5);
        expect(Interval::quality('d5'))->toBe('d');
        expect(Interval::semitones('d5'))->toBe(6);
    });

    test('distance', function () {
        expect(Interval::distance('C4', 'G4'))->toBe('5P');
    });

    test('names', function () {
        expect(Interval::names())->toBe([
            '1P', '2M', '3M', '4P', '5P', '6m', '7m',
        ]);
    });

    test('simplify intervals', function () {
        $split = fn ($s) => explode(' ', $s);

        expect(array_map(Interval::simplify(...), $split('1P 2M 3M 4P 5P 6M 7M')))
            ->toBe($split('1P 2M 3M 4P 5P 6M 7M'));

        expect(array_map(Interval::simplify(...), $split('8P 9M 10M 11P 12P 13M 14M')))
            ->toBe($split('8P 2M 3M 4P 5P 6M 7M'));

        expect(array_map(Interval::simplify(...), $split('1d 1P 1A 8d 8P 8A 15d 15P 15A')))
            ->toBe($split('1d 1P 1A 8d 8P 8A 1d 1P 1A'));

        expect(array_map(Interval::simplify(...), $split('-1P -2M -3M -4P -5P -6M -7M')))
            ->toBe($split('-1P -2M -3M -4P -5P -6M -7M'));

        expect(array_map(Interval::simplify(...), $split('-8P -9M -10M -11P -12P -13M -14M')))
            ->toBe($split('-8P -2M -3M -4P -5P -6M -7M'));
    });

    test('invert intervals', function () {
        $split = fn ($s) => explode(' ', $s);

        expect(array_map(Interval::invert(...), $split('1P 2M 3M 4P 5P 6M 7M')))
            ->toBe($split('1P 7m 6m 5P 4P 3m 2m'));

        expect(array_map(Interval::invert(...), $split('1d 2m 3m 4d 5d 6m 7m')))
            ->toBe($split('1A 7M 6M 5A 4A 3M 2M'));

        expect(array_map(Interval::invert(...), $split('1A 2A 3A 4A 5A 6A 7A')))
            ->toBe($split('1d 7d 6d 5d 4d 3d 2d'));

        expect(array_map(Interval::invert(...), $split('-1P -2M -3M -4P -5P -6M -7M')))
            ->toBe($split('-1P -7m -6m -5P -4P -3m -2m'));

        expect(array_map(Interval::invert(...), $split('8P 9M 10M 11P 12P 13M 14M')))
            ->toBe($split('8P 14m 13m 12P 11P 10m 9m'));
    });

    test('fromSemitones', function () {
        $semis = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        expect(array_map(Interval::fromSemitones(...), $semis))
            ->toBe(explode(' ', '1P 2m 2M 3m 3M 4P 5d 5P 6m 6M 7m 7M'));

        $semis = [12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
        expect(array_map(Interval::fromSemitones(...), $semis))
            ->toBe(explode(' ', '8P 9m 9M 10m 10M 11P 12d 12P 13m 13M 14m 14M'));

        $semis = [0, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10, -11];
        expect(array_map(Interval::fromSemitones(...), $semis))
            ->toBe(explode(' ', '1P -2m -2M -3m -3M -4P -5d -5P -6m -6M -7m -7M'));

        $semis = [-12, -13, -14, -15, -16, -17, -18, -19, -20, -21, -22, -23];
        expect(array_map(Interval::fromSemitones(...), $semis))
            ->toBe(explode(' ', '-8P -9m -9M -10m -10M -11P -12d -12P -13m -13M -14m -14M'));
    });

    test('add', function () {
        expect(Interval::add('3m', '5P'))->toBe('7m');

        $addTo5P = fn ($n) => Interval::add('5P', $n);
        expect(array_map($addTo5P, Interval::names()))
            ->toBe(explode(' ', '5P 6M 7M 8P 9M 10m 11P'));

        $addToFn = Interval::addTo('5P');
        expect(array_map($addToFn, Interval::names()))
            ->toBe(explode(' ', '5P 6M 7M 8P 9M 10m 11P'));
    });

    test('subtract', function () {
        expect(Interval::subtract('5P', '3M'))->toBe('3m');
        expect(Interval::subtract('3M', '5P'))->toBe('-3m');

        $subtractFrom5P = fn ($n) => Interval::subtract('5P', $n);
        expect(array_map($subtractFrom5P, Interval::names()))
            ->toBe(explode(' ', '5P 4P 3m 2M 1P -2m -3m'));
    });

    test('transposeFifths', function () {
        expect(Interval::transposeFifths('4P', 1))->toBe('8P');

        $transpose = fn ($fifths) => Interval::transposeFifths('1P', $fifths);
        expect(implode(' ', array_map($transpose, [0, 1, 2, 3, 4])))->toBe('1P 5P 9M 13M 17M');
        expect(implode(' ', array_map($transpose, [0, -1, -2, -3, -4])))->toBe('1P -5P -9M -13M -17M');
    });
});
