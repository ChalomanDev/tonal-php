<?php

declare(strict_types=1);

use Chaloman\Tonal\ChordType;
use Chaloman\Tonal\Data\ChordTypeData;
use Chaloman\Tonal\Enums\ChordQuality;
use Chaloman\Tonal\PitchInterval;

beforeEach(function () {
    // Reset the dictionary before each test
    ChordType::resetForTesting();
});

describe('ChordType', function () {
    test('names returns chord names sorted by setNum', function () {
        $names = ChordType::names();

        // First 5 names (sorted by setNum)
        expect(array_slice($names, 0, 5))->toBe([
            'fifth',
            'suspended fourth',
            'suspended fourth seventh',
            'augmented',
            'major seventh flat sixth',
        ]);
    });

    test('symbols returns first alias of each chord', function () {
        $symbols = ChordType::symbols();

        // First 3 symbols (sorted by setNum)
        expect(array_slice($symbols, 0, 3))->toBe([
            '5',
            'M7#5sus4',
            '7#5sus4',
        ]);
    });

    test('all returns all chords', function () {
        expect(count(ChordType::all()))->toBe(106);
    });

    test('get returns chord type by name', function () {
        $major = ChordType::get('major');

        expect($major->toArray())->toMatchArray([
            'empty' => false,
            'setNum' => 2192,
            'name' => 'major',
            'quality' => 'Major',
            'intervals' => ['1P', '3M', '5P'],
            'aliases' => ['M', '^', '', 'maj'],
            'chroma' => '100010010000',
            'normalized' => '100001000100',
        ]);
    });

    test('get returns chord type by alias', function () {
        expect(ChordType::get('m7'))->toEqual(ChordType::get('minor seventh'))
            ->and(ChordType::get('M7'))->toEqual(ChordType::get('major seventh'))
            ->and(ChordType::get('7'))->toEqual(ChordType::get('dominant seventh'));
    });

    test('get returns chord type by chroma', function () {
        $major = ChordType::get('major');
        expect(ChordType::get($major->chroma))->toEqual($major);
    });

    test('get returns chord type by setNum', function () {
        $major = ChordType::get('major');
        expect(ChordType::get($major->setNum))->toEqual($major);
    });

    test('get returns empty chord type for unknown', function () {
        $unknown = ChordType::get('unknown chord');
        expect($unknown->empty)->toBeTrue()
            ->and($unknown->quality)->toBe(ChordQuality::Unknown);
    });

    test('add a chord', function () {
        ChordType::add(['1P', '5P'], ['q']);
        $chord = ChordType::get('q');

        expect($chord->chroma)->toBe('100000010000');

        ChordType::add(['1P', '5P'], ['q'], 'quinta');
        expect(ChordType::get('quinta'))->toEqual(ChordType::get('q'));
    });

    test('removeAll clears dictionary', function () {
        // Ensure dictionary is populated
        expect(count(ChordType::all()))->toBeGreaterThan(0);

        ChordType::removeAll();

        expect(ChordType::all())->toBe([])
            ->and(ChordType::keys())->toBe([]);
    });

    test('chord quality detection', function () {
        expect(ChordType::get('major')->quality)->toBe(ChordQuality::Major)
            ->and(ChordType::get('minor')->quality)->toBe(ChordQuality::Minor)
            ->and(ChordType::get('augmented')->quality)->toBe(ChordQuality::Augmented)
            ->and(ChordType::get('diminished')->quality)->toBe(ChordQuality::Diminished)
            ->and(ChordType::get('fifth')->quality)->toBe(ChordQuality::Unknown);
    });
});

describe('ChordType data validation', function () {
    test('no repeated intervals in data', function () {
        $data = ChordTypeData::getData();
        $intervals = array_map(fn ($d) => $d[0], $data);
        sort($intervals);

        for ($i = 1; $i < count($intervals); $i++) {
            expect($intervals[$i - 1])->not->toBe($intervals[$i]);
        }
    });

    test('all chords must have aliases', function () {
        $data = ChordTypeData::getData();

        foreach ($data as $chord) {
            expect(strlen(trim($chord[2])))->toBeGreaterThan(0);
        }
    });

    test('intervals should be in ascending order', function () {
        $data = ChordTypeData::getData();

        foreach ($data as $chord) {
            $intervalNames = explode(' ', $chord[0]);
            $semitones = array_map(
                fn ($i) => PitchInterval::interval($i)->semitones,
                $intervalNames,
            );

            for ($i = 1; $i < count($semitones); $i++) {
                expect($semitones[$i - 1])->toBeLessThan(
                    $semitones[$i],
                    "Chord '{$chord[1]}' has intervals not in ascending order: {$chord[0]}",
                );
            }
        }
    });
});
