<?php

declare(strict_types=1);

use Chaloman\Tonal\Direction;
use Chaloman\Tonal\Pitch;

// Pitch classes (sin octava)
$C = new Pitch(step: 0, alt: 0);
$Cs = new Pitch(step: 0, alt: 1);
$Cb = new Pitch(step: 0, alt: -1);
$A = new Pitch(step: 5, alt: 0);

// Notes (con octava)
$C4 = new Pitch(step: 0, alt: 0, oct: 4);
$A4 = new Pitch(step: 5, alt: 0, oct: 4);
$Gs6 = new Pitch(step: 4, alt: 1, oct: 6);

// Intervals (con dirección)
$P5 = new Pitch(step: 4, alt: 0, oct: 0, dir: Direction::Ascending);
$P_5 = new Pitch(step: 4, alt: 0, oct: 0, dir: Direction::Descending);

describe('Pitch', function () use ($C, $Cs, $Cb, $A, $C4, $A4, $Gs6, $P5, $P_5) {

    test('isNamedPitch returns false for Pitch objects', function () use ($C) {
        expect(Pitch::isNamedPitch($C))->toBeFalse()
            ->and(Pitch::isNamedPitch(['name' => 'C']))->toBeFalse()
            ->and(Pitch::isNamedPitch(null))->toBeFalse();
    });

    test('isPitch validates Pitch objects', function () use ($C) {
        expect(Pitch::isPitch($C))->toBeTrue()
            ->and(Pitch::isPitch(null))->toBeFalse()
            ->and(Pitch::isPitch('C'))->toBeFalse()
            ->and(Pitch::isPitch(['step' => 0, 'alt' => 0]))->toBeFalse();
    });

    test('height calculates correctly for pitch classes', function () use ($C, $Cs, $Cb, $A) {
        expect(Pitch::height($C))->toBe(-1200)
            ->and(Pitch::height($Cs))->toBe(-1199)
            ->and(Pitch::height($Cb))->toBe(-1201)
            ->and(Pitch::height($A))->toBe(-1191);
    });

    test('height calculates correctly for notes', function () use ($C4, $A4, $Gs6) {
        expect(Pitch::height($C4))->toBe(48)
            ->and(Pitch::height($A4))->toBe(57)
            ->and(Pitch::height($Gs6))->toBe(80);
    });

    test('height calculates correctly for intervals', function () use ($P5, $P_5) {
        expect(Pitch::height($P5))->toBe(7)
            ->and(Pitch::height($P_5))->toBe(-7);
    });

    test('midi returns null for pitch classes', function () use ($C, $Cs, $Cb, $A) {
        expect(Pitch::midi($C))->toBeNull()
            ->and(Pitch::midi($Cs))->toBeNull()
            ->and(Pitch::midi($Cb))->toBeNull()
            ->and(Pitch::midi($A))->toBeNull();
    });

    test('midi returns correct values for notes', function () use ($C4, $A4, $Gs6) {
        expect(Pitch::midi($C4))->toBe(60)
            ->and(Pitch::midi($A4))->toBe(69)
            ->and(Pitch::midi($Gs6))->toBe(92);
    });

    test('chroma calculates correctly for pitch classes', function () use ($C, $Cs, $Cb, $A) {
        expect(Pitch::chroma($C))->toBe(0)
            ->and(Pitch::chroma($Cs))->toBe(1)
            ->and(Pitch::chroma($Cb))->toBe(11)
            ->and(Pitch::chroma($A))->toBe(9);
    });

    test('chroma calculates correctly for notes', function () use ($C4, $A4, $Gs6) {
        expect(Pitch::chroma($C4))->toBe(0)
            ->and(Pitch::chroma($A4))->toBe(9)
            ->and(Pitch::chroma($Gs6))->toBe(8);
    });

    test('chroma calculates correctly for intervals', function () use ($P5, $P_5) {
        expect(Pitch::chroma($P5))->toBe(7)
            ->and(Pitch::chroma($P_5))->toBe(7);
    });

    test('coordinates for pitch classes', function () use ($C, $A, $Cs, $Cb) {
        expect(Pitch::coordinates($C))->toBe([0])
            ->and(Pitch::coordinates($A))->toBe([3])
            ->and(Pitch::coordinates($Cs))->toBe([7])
            ->and(Pitch::coordinates($Cb))->toBe([-7]);
    });

    test('coordinates for notes', function () use ($C4, $A4) {
        expect(Pitch::coordinates($C4))->toBe([0, 4])
            ->and(Pitch::coordinates($A4))->toBe([3, 3]);
    });

    test('coordinates for intervals', function () use ($P5, $P_5) {
        expect(Pitch::coordinates($P5))->toBe([1, 0])
            ->and(Pitch::coordinates($P_5))->toBe([-1, 0]);
    });

    test('fromCoordinates creates pitch class', function () use ($C, $Cs) {
        $pitch = Pitch::fromCoordinates([0]);
        expect($pitch->toArray())->toBe(['step' => 0, 'alt' => 0]);

        $pitch = Pitch::fromCoordinates([7]);
        expect($pitch->toArray())->toBe(['step' => 0, 'alt' => 1]);
    });

    test('toArray returns correct structure', function () use ($C4, $P5) {
        expect($C4->toArray())->toBe([
            'step' => 0,
            'alt' => 0,
            'oct' => 4,
        ])
            ->and($P5->toArray())->toBe([
                'step' => 4,
                'alt' => 0,
                'oct' => 0,
                'dir' => 1,
            ]);

    });
});
