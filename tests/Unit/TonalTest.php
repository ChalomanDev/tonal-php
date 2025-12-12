<?php

declare(strict_types=1);

use Chaloman\Tonal\ChordObject;
use Chaloman\Tonal\MajorKey;
use Chaloman\Tonal\MinorKey;
use Chaloman\Tonal\PitchInterval;
use Chaloman\Tonal\PitchNote;
use Chaloman\Tonal\RomanNumeral;
use Chaloman\Tonal\ScaleObject;
use Chaloman\Tonal\Tonal;

describe('Tonal', function () {
    describe('version', function () {
        test('has version constant', function () {
            expect(Tonal::VERSION)->toBe('1.0.0');
        });
    });

    describe('Core functions', function () {
        test('transpose', function () {
            expect(Tonal::transpose('C4', '3M'))->toBe('E4')
                ->and(Tonal::transpose('D4', '5P'))->toBe('A4')
                ->and(Tonal::transpose('G', '2M'))->toBe('A');
        });

        test('distance', function () {
            expect(Tonal::distance('C4', 'E4'))->toBe('3M')
                ->and(Tonal::distance('C4', 'G4'))->toBe('5P')
                ->and(Tonal::distance('C', 'D'))->toBe('2M');
        });
    });

    describe('note()', function () {
        test('returns PitchNote object', function () {
            $note = Tonal::note('C4');
            expect($note)->toBeInstanceOf(PitchNote::class)
                ->and($note->name)->toBe('C4')
                ->and($note->midi)->toBe(60);
        });

        test('handles sharps and flats', function () {
            expect(Tonal::note('F#4')->chroma)->toBe(6)
                ->and(Tonal::note('Bb3')->chroma)->toBe(10);
        });
    });

    describe('interval()', function () {
        test('returns PitchInterval object', function () {
            $interval = Tonal::interval('5P');
            expect($interval)->toBeInstanceOf(PitchInterval::class)
                ->and($interval->name)->toBe('5P')
                ->and($interval->semitones)->toBe(7);
        });

        test('handles complex intervals', function () {
            expect(Tonal::interval('9M')->semitones)->toBe(14)
                ->and(Tonal::interval('3m')->semitones)->toBe(3);
        });
    });

    describe('chord()', function () {
        test('returns ChordObject', function () {
            $chord = Tonal::chord('Cmaj7');
            expect($chord)->toBeInstanceOf(ChordObject::class)
                ->and($chord->name)->toBe('C major seventh')
                ->and($chord->symbol)->toBe('Cmaj7');
        });

        test('handles chord inversions', function () {
            $chord = Tonal::chord('C/E');
            expect($chord->tonic)->toBe('C')
                ->and($chord->bass)->toBe('E')
                ->and($chord->root)->toBe('E');
        });
    });

    describe('chordDetect()', function () {
        test('detects chords from notes', function () {
            $detected = Tonal::chordDetect(['C', 'E', 'G']);
            expect($detected)->toContain('CM');
        });

        test('detects seventh chords', function () {
            $detected = Tonal::chordDetect(['C', 'E', 'G', 'B']);
            expect($detected)->toContain('Cmaj7');
        });
    });

    describe('scale()', function () {
        test('returns ScaleObject', function () {
            $scale = Tonal::scale('C major');
            expect($scale)->toBeInstanceOf(ScaleObject::class)
                ->and($scale->name)->toBe('C major')
                ->and($scale->notes)->toBe(['C', 'D', 'E', 'F', 'G', 'A', 'B']);
        });

        test('handles minor scales', function () {
            $scale = Tonal::scale('A minor');
            expect($scale->notes)->toBe(['A', 'B', 'C', 'D', 'E', 'F', 'G']);
        });
    });

    describe('majorKey()', function () {
        test('returns MajorKey object', function () {
            $key = Tonal::majorKey('C');
            expect($key)->toBeInstanceOf(MajorKey::class)
                ->and($key->tonic)->toBe('C')
                ->and($key->type)->toBe('major');
        });

        test('provides scale degrees', function () {
            $key = Tonal::majorKey('G');
            expect($key->scale)->toBe(['G', 'A', 'B', 'C', 'D', 'E', 'F#']);
        });
    });

    describe('minorKey()', function () {
        test('returns MinorKey object', function () {
            $key = Tonal::minorKey('A');
            expect($key)->toBeInstanceOf(MinorKey::class)
                ->and($key->tonic)->toBe('A')
                ->and($key->type)->toBe('minor');
        });

        test('provides natural scale', function () {
            $key = Tonal::minorKey('E');
            expect($key->natural->scale)->toBe(['E', 'F#', 'G', 'A', 'B', 'C', 'D']);
        });
    });

    describe('MIDI functions', function () {
        test('toMidi converts note to MIDI number', function () {
            expect(Tonal::toMidi('C4'))->toBe(60)
                ->and(Tonal::toMidi('A4'))->toBe(69)
                ->and(Tonal::toMidi(60))->toBe(60);
        });

        test('midiToNoteName converts MIDI to note', function () {
            expect(Tonal::midiToNoteName(60))->toBe('C4')
                ->and(Tonal::midiToNoteName(69))->toBe('A4');
        });

        test('midiToNoteName with options', function () {
            expect(Tonal::midiToNoteName(61, ['sharps' => true]))->toBe('C#4')
                ->and(Tonal::midiToNoteName(61, ['sharps' => false]))->toBe('Db4');
        });
    });

    describe('romanNumeral()', function () {
        test('returns RomanNumeral object', function () {
            $rn = Tonal::romanNumeral('IV');
            expect($rn)->toBeInstanceOf(RomanNumeral::class)
                ->and($rn->name)->toBe('IV');
        });

        test('handles minor numerals', function () {
            $rn = Tonal::romanNumeral('vi');
            expect($rn->name)->toBe('vi');
        });
    });

    describe('Progression functions', function () {
        test('fromRomanNumerals converts to chord names', function () {
            $chords = Tonal::fromRomanNumerals('C', ['I', 'IV', 'V']);
            expect($chords)->toBe(['C', 'F', 'G']);
        });

        test('toRomanNumerals converts to numerals', function () {
            $numerals = Tonal::toRomanNumerals('C', ['C', 'F', 'G']);
            expect($numerals)->toBe(['I', 'IV', 'V']);
        });
    });

    describe('modules()', function () {
        test('returns list of all modules', function () {
            $modules = Tonal::modules();

            expect($modules)->toBeArray()
                ->and($modules)->toHaveKey('Note')
                ->and($modules)->toHaveKey('Chord')
                ->and($modules)->toHaveKey('Scale')
                ->and($modules)->toHaveKey('Interval')
                ->and($modules)->toHaveKey('Key')
                ->and($modules)->toHaveKey('Midi')
                ->and($modules)->toHaveKey('Core');
        });

        test('all modules are valid classes', function () {
            $modules = Tonal::modules();

            foreach ($modules as $name => $className) {
                expect(class_exists($className))->toBeTrue("Module {$name} class {$className} does not exist");
            }
        });

        test('contains all expected modules', function () {
            $modules = Tonal::modules();
            $expectedModules = [
                'AbcNotation',
                'Chord',
                'ChordDetect',
                'ChordType',
                'Collection',
                'Core',
                'DurationValue',
                'Interval',
                'Key',
                'Midi',
                'Mode',
                'Note',
                'Pcset',
                'Pitch',
                'PitchDistance',
                'PitchInterval',
                'PitchNote',
                'Progression',
                'Range',
                'RhythmPattern',
                'RomanNumeral',
                'Scale',
                'ScaleType',
                'TimeSignature',
                'TonalArray',
                'VoiceLeading',
                'Voicing',
                'VoicingDictionary',
            ];

            foreach ($expectedModules as $moduleName) {
                expect($modules)->toHaveKey($moduleName);
            }
        });
    });
});
