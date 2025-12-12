<?php

declare(strict_types=1);

use Chaloman\Tonal\PitchInterval;
use Chaloman\Tonal\RomanNumeral;

describe('RomanNumeral', function () {
    test('names returns major roman numerals', function () {
        expect(RomanNumeral::names())->toBe([
            'I', 'II', 'III', 'IV', 'V', 'VI', 'VII',
        ]);
    });

    test('names returns minor roman numerals', function () {
        expect(RomanNumeral::names(false))->toBe([
            'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii',
        ]);
    });

    test('get returns properties for #VIIb5', function () {
        $rn = RomanNumeral::get('#VIIb5');

        expect($rn->toArray())->toMatchArray([
            'empty' => false,
            'name' => '#VIIb5',
            'roman' => 'VII',
            'interval' => '7A',
            'acc' => '#',
            'chordType' => 'b5',
            'major' => true,
            'step' => 6,
            'alt' => 1,
            'oct' => 0,
            'dir' => 1,
        ]);
    });

    test('RomanNumeral is compatible with PitchInterval', function () {
        // Natural intervals
        $naturals = array_map(
            fn ($s) => PitchInterval::interval($s),
            explode(' ', '1P 2M 3M 4P 5P 6M 7M'),
        );
        $romanNames = array_map(
            fn ($ivl) => RomanNumeral::get($ivl)->name,
            $naturals,
        );
        expect($romanNames)->toBe(explode(' ', 'I II III IV V VI VII'));

        // Flat intervals
        $flats = array_map(
            fn ($s) => PitchInterval::interval($s),
            explode(' ', '1d 2m 3m 4d 5d 6m 7m'),
        );
        $romanFlats = array_map(
            fn ($ivl) => RomanNumeral::get($ivl)->name,
            $flats,
        );
        expect($romanFlats)->toBe(explode(' ', 'bI bII bIII bIV bV bVI bVII'));

        // Sharp intervals
        $sharps = array_map(
            fn ($s) => PitchInterval::interval($s),
            explode(' ', '1A 2A 3A 4A 5A 6A 7A'),
        );
        $romanSharps = array_map(
            fn ($ivl) => RomanNumeral::get($ivl)->name,
            $sharps,
        );
        expect($romanSharps)->toBe(explode(' ', '#I #II #III #IV #V #VI #VII'));
    });

    test('can convert to intervals', function () {
        $ivl1 = PitchInterval::interval(RomanNumeral::get('I'));
        expect($ivl1->name)->toBe('1P');

        $ivl2 = PitchInterval::interval(RomanNumeral::get('bIIImaj4'));
        expect($ivl2->name)->toBe('3m');

        $ivl3 = PitchInterval::interval(RomanNumeral::get('#IV7'));
        expect($ivl3->name)->toBe('4A');
    });

    test('step is correct', function () {
        $steps = array_map(
            fn ($name) => RomanNumeral::get($name)->step,
            RomanNumeral::names(),
        );
        expect($steps)->toBe([0, 1, 2, 3, 4, 5, 6]);
    });

    test('invalid returns empty', function () {
        expect(RomanNumeral::get('nothing')->name)->toBe('');
        expect(RomanNumeral::get('iI')->name)->toBe('');
    });

    test('roman property is correct', function () {
        expect(RomanNumeral::get('IIIMaj7')->roman)->toBe('III');

        $romanNames = array_map(
            fn ($x) => RomanNumeral::get($x)->name,
            RomanNumeral::names(),
        );
        expect($romanNames)->toBe(RomanNumeral::names());
    });

    test('create from degrees', function () {
        $names = array_map(
            fn ($i) => RomanNumeral::get($i - 1)->name,
            [1, 2, 3, 4, 5, 6, 7],
        );
        expect($names)->toBe(RomanNumeral::names());
    });

    test('tokenize works correctly', function () {
        expect(RomanNumeral::tokenize('#VIIb5'))->toBe(['#VIIb5', '#', 'VII', 'b5']);
        expect(RomanNumeral::tokenize('bIII'))->toBe(['bIII', 'b', 'III', '']);
        expect(RomanNumeral::tokenize('iv'))->toBe(['iv', '', 'iv', '']);
        expect(RomanNumeral::tokenize('invalid'))->toBe(['', '', '', '']);
    });
});
