<?php

declare(strict_types=1);

use Chaloman\Tonal\Pcset;
use Chaloman\Tonal\ScaleType;

beforeEach(function () {
    // Reset the dictionary before each test
    ScaleType::resetForTesting();
});

describe('ScaleType', function () {
    test('all returns all scales', function () {
        expect(count(ScaleType::all()))->toBe(92)
            ->and(ScaleType::all()[0]->name)->toBe('major pentatonic');
    });

    test('get returns scale type by name', function () {
        $major = ScaleType::get('major');

        expect($major->toArray())->toMatchArray([
            'empty' => false,
            'setNum' => 2773,
            'name' => 'major',
            'intervals' => ['1P', '2M', '3M', '4P', '5P', '6M', '7M'],
            'aliases' => ['ionian'],
            'chroma' => '101011010101',
            'normalized' => '101010110101',
        ]);
    });

    test('get returns empty for unknown scale', function () {
        $unknown = ScaleType::get('unknown');

        expect($unknown->toArray())->toMatchArray([
            'empty' => true,
            'name' => '',
            'setNum' => 0,
            'aliases' => [],
            'chroma' => '000000000000',
            'intervals' => [],
            'normalized' => '000000000000',
        ]);
    });

    test('get returns scale type by alias', function () {
        expect(ScaleType::get('ionian'))->toEqual(ScaleType::get('major'))
            ->and(ScaleType::get('aeolian'))->toEqual(ScaleType::get('minor'))
            ->and(ScaleType::get('pentatonic'))->toEqual(ScaleType::get('major pentatonic'));
    });

    test('get returns scale type by chroma', function () {
        $major = ScaleType::get('major');
        expect(ScaleType::get($major->chroma))->toEqual($major);
    });

    test('get returns scale type by setNum', function () {
        $major = ScaleType::get('major');
        expect(ScaleType::get($major->setNum))->toEqual($major);
    });

    test('add a scale type', function () {
        ScaleType::add(['1P', '5P'], 'quinta');
        $scale = ScaleType::get('quinta');

        expect($scale->chroma)->toBe('100000010000');

        ScaleType::add(['1P', '5P'], 'quinta', ['q', 'Q']);
        expect(ScaleType::get('q'))->toEqual(ScaleType::get('quinta'))
            ->and(ScaleType::get('Q'))->toEqual(ScaleType::get('quinta'));
    });

    test('major modes', function () {
        $chromas = Pcset::modes(ScaleType::get('major')->intervals, true);
        $names = array_map(fn ($chroma) => ScaleType::get($chroma)->name, $chromas);

        expect($names)->toBe([
            'major',
            'dorian',
            'phrygian',
            'lydian',
            'mixolydian',
            'minor',
            'locrian',
        ]);
    });

    test('harmonic minor modes', function () {
        $chromas = Pcset::modes(ScaleType::get('harmonic minor')->intervals, true);
        $names = array_map(fn ($chroma) => ScaleType::get($chroma)->name, $chromas);

        expect($names)->toBe([
            'harmonic minor',
            'locrian 6',
            'major augmented',
            'dorian #4',
            'phrygian dominant',
            'lydian #9',
            'ultralocrian',
        ]);
    });

    test('melodic minor modes', function () {
        $chromas = Pcset::modes(ScaleType::get('melodic minor')->intervals, true);
        $names = array_map(fn ($chroma) => ScaleType::get($chroma)->name, $chromas);

        expect($names)->toBe([
            'melodic minor',
            'dorian b2',
            'lydian augmented',
            'lydian dominant',
            'mixolydian b6',
            'locrian #2',
            'altered',
        ]);
    });

    test('removeAll clears dictionary', function () {
        // Ensure dictionary is populated
        expect(count(ScaleType::all()))->toBeGreaterThan(0);

        ScaleType::removeAll();

        expect(ScaleType::all())->toBe([])
            ->and(ScaleType::keys())->toBe([]);
    });

    test('names returns all scale names', function () {
        $names = ScaleType::names();

        expect(count($names))->toBe(92)
            ->and($names[0])->toBe('major pentatonic')
            ->and(in_array('major', $names, true))->toBeTrue()
            ->and(in_array('minor', $names, true))->toBeTrue();
    });
});
