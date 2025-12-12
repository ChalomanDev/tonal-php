<?php

declare(strict_types=1);

use Chaloman\Tonal\Progression;

describe('Progression', function () {
    test('fromRomanNumerals', function () {
        $inC = fn(array $chords) => Progression::fromRomanNumerals('C', $chords);

        expect($inC(explode(' ', 'I IIm7 V7')))->toBe(explode(' ', 'C Dm7 G7'));
        expect($inC(explode(' ', 'Imaj7 2 IIIm7')))->toBe(['Cmaj7', '', 'Em7']);
        expect($inC(explode(' ', 'I II III IV V VI VII')))->toBe(explode(' ', 'C D E F G A B'));
        expect($inC(explode(' ', 'bI bII bIII bIV bV bVI bVII')))->toBe(explode(' ', 'Cb Db Eb Fb Gb Ab Bb'));
        expect($inC(explode(' ', '#Im7 #IIm7 #III #IVMaj7 #V7 #VI #VIIo')))
            ->toBe(explode(' ', 'C#m7 D#m7 E# F#Maj7 G#7 A# B#o'));
    });

    test('toRomanNumerals', function () {
        $roman = Progression::toRomanNumerals('C', ['Cmaj7', 'Dm7', 'G7']);
        expect($roman)->toBe(['Imaj7', 'IIm7', 'V7']);
    });

    test('fromRomanNumerals with different tonics', function () {
        expect(Progression::fromRomanNumerals('G', ['I', 'IV', 'V']))
            ->toBe(['G', 'C', 'D']);

        expect(Progression::fromRomanNumerals('F', ['Im7', 'IVm7', 'V7']))
            ->toBe(['Fm7', 'Bbm7', 'C7']);
    });

    test('toRomanNumerals with different tonics', function () {
        expect(Progression::toRomanNumerals('G', ['G', 'C', 'D']))
            ->toBe(['I', 'IV', 'V']);

        expect(Progression::toRomanNumerals('A', ['A', 'D', 'E7']))
            ->toBe(['I', 'IV', 'V7']);
    });

    test('handles empty chords', function () {
        expect(Progression::fromRomanNumerals('C', ['I', '', 'V']))
            ->toBe(['C', '', 'G']);
    });
});
