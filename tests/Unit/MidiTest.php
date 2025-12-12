<?php

declare(strict_types=1);

use Chaloman\Tonal\Midi;

describe('midi', function () {

    test('isMidi', function () {
        expect(Midi::isMidi(100))->toBeTrue();
        expect(Midi::isMidi(0))->toBeTrue();
        expect(Midi::isMidi(127))->toBeTrue();
        expect(Midi::isMidi(-1))->toBeFalse();
        expect(Midi::isMidi(128))->toBeFalse();
        expect(Midi::isMidi('invalid'))->toBeFalse();
    });

    test('toMidi', function () {
        expect(Midi::toMidi(100))->toBe(100);
        expect(Midi::toMidi('C4'))->toBe(60);
        expect(Midi::toMidi('60'))->toBe(60);
        expect(Midi::toMidi(0))->toBe(0);
        expect(Midi::toMidi('0'))->toBe(0);
        expect(Midi::toMidi(-1))->toBeNull();
        expect(Midi::toMidi(128))->toBeNull();
        expect(Midi::toMidi('blah'))->toBeNull();
    });

    test('freqToMidi', function () {
        expect(Midi::freqToMidi(220))->toBe(57.0);
        expect(Midi::freqToMidi(261.62))->toBe(60.0);
        expect(Midi::freqToMidi(261))->toBe(59.96);
    });

    test('midiToFreq', function () {
        expect(Midi::midiToFreq(60))->toEqualWithDelta(261.6255653005986, 0.0001);
        expect(Midi::midiToFreq(69, 443))->toBe(443.0);
    });

    test('midiToNoteName', function () {
        $notes = [60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72];

        expect(implode(' ', array_map(fn ($m) => Midi::midiToNoteName($m), $notes)))
            ->toBe('C4 Db4 D4 Eb4 E4 F4 Gb4 G4 Ab4 A4 Bb4 B4 C5');

        expect(implode(' ', array_map(fn ($n) => Midi::midiToNoteName($n, ['sharps' => true]), $notes)))
            ->toBe('C4 C#4 D4 D#4 E4 F4 F#4 G4 G#4 A4 A#4 B4 C5');

        expect(implode(' ', array_map(fn ($n) => Midi::midiToNoteName($n, ['pitchClass' => true]), $notes)))
            ->toBe('C Db D Eb E F Gb G Ab A Bb B C');

        expect(Midi::midiToNoteName(NAN))->toBe('');
        expect(Midi::midiToNoteName(-INF))->toBe('');
        expect(Midi::midiToNoteName(INF))->toBe('');
    });

    describe('Midi::pcset', function () {

        test('from chroma', function () {
            expect(Midi::pcset('100100100101'))->toBe([0, 3, 6, 9, 11]);
        });

        test('from midi', function () {
            expect(Midi::pcset([62, 63, 60, 65, 70, 72]))->toBe([0, 2, 3, 5, 10]);
        });
    });

    describe('Midi::pcsetNearest', function () {

        test('find nearest upwards', function () {
            $nearest = Midi::pcsetNearest([0, 5, 7]);

            expect(array_map($nearest, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]))
                ->toBe([0, 0, 0, 5, 5, 5, 7, 7, 7, 7, 12, 12, 12]);
        });

        test('chromatic to nearest C minor pentatonic', function () {
            $nearest = Midi::pcsetNearest('100101010010');

            expect(array_map($nearest, [36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47]))
                ->toBe([36, 36, 39, 39, 41, 41, 43, 43, 43, 46, 46, 48]);
        });

        test('chromatic to nearest half octave', function () {
            $nearest = Midi::pcsetNearest('100000100000');

            expect(array_map($nearest, [36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47]))
                ->toBe([36, 36, 36, 42, 42, 42, 42, 42, 42, 48, 48, 48]);
        });

        test('empty pcsets returns null', function () {
            $nearest = Midi::pcsetNearest([]);

            expect(array_map($nearest, [10, 30, 40]))->toBe([null, null, null]);
        });
    });

    test('Midi::pcsetSteps', function () {
        $scale = Midi::pcsetSteps('101010', 60);

        expect(array_map($scale, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]))
            ->toBe([60, 62, 64, 72, 74, 76, 84, 86, 88, 96]);

        expect(array_map($scale, [0, -1, -2, -3, -4, -5, -6, -7, -8, -9]))
            ->toBe([60, 52, 50, 48, 40, 38, 36, 28, 26, 24]);
    });

    test('Midi::pcsetDegrees', function () {
        $scale = Midi::pcsetDegrees('101010', 60);

        expect(array_map($scale, [1, 2, 3, 4, 5]))->toBe([60, 62, 64, 72, 74]);
        expect(array_map($scale, [-1, -2, -3, 4, 5]))->toBe([52, 50, 48, 72, 74]);
        expect($scale(0))->toBeNull();
    });
});
