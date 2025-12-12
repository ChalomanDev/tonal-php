<?php

declare(strict_types=1);

use Chaloman\Tonal\Scale;

describe('Scale', function () {
    test('get', function () {
        $major = Scale::get('major');
        expect($major->empty)->toBeFalse()
            ->and($major->tonic)->toBeNull()
            ->and($major->notes)->toBe([])
            ->and($major->type)->toBe('major')
            ->and($major->name)->toBe('major')
            ->and($major->intervals)->toBe(['1P', '2M', '3M', '4P', '5P', '6M', '7M'])
            ->and($major->aliases)->toBe(['ionian'])
            ->and($major->setNum)->toBe(2773)
            ->and($major->chroma)->toBe('101011010101')
            ->and($major->normalized)->toBe('101010110101');

        $penta = Scale::get('c5 pentatonic');
        expect($penta->empty)->toBeFalse()
            ->and($penta->name)->toBe('C5 major pentatonic')
            ->and($penta->type)->toBe('major pentatonic')
            ->and($penta->tonic)->toBe('C5')
            ->and($penta->notes)->toBe(['C5', 'D5', 'E5', 'G5', 'A5'])
            ->and($penta->intervals)->toBe(['1P', '2M', '3M', '5P', '6M'])
            ->and($penta->aliases)->toBe(['pentatonic'])
            ->and($penta->setNum)->toBe(2708)
            ->and($penta->chroma)->toBe('101010010100')
            ->and($penta->normalized)->toBe('100101001010')
            ->and(Scale::get('C4 major')->name)->toBe(Scale::get(['C4', 'major'])->name)
            ->and(Scale::get('C4 Major')->name)->toBe(Scale::get('C4 major')->name);

    });

    test('tokenize', function () {
        expect(Scale::tokenize('c major'))->toBe(['C', 'major'])
            ->and(Scale::tokenize('cb3 major'))->toBe(['Cb3', 'major'])
            ->and(Scale::tokenize('melodic minor'))->toBe(['', 'melodic minor'])
            ->and(Scale::tokenize('dorian'))->toBe(['', 'dorian'])
            ->and(Scale::tokenize('c'))->toBe(['C', ''])
            ->and(Scale::tokenize(''))->toBe(['', '']);
    });

    test('isKnown', function () {
        expect(Scale::get('major')->empty)->toBeFalse()
            ->and(Scale::get('Db major')->empty)->toBeFalse()
            ->and(Scale::get('hello')->empty)->toBeTrue()
            ->and(Scale::get('')->empty)->toBeTrue()
            ->and(Scale::get('Maj7')->empty)->toBeTrue();
    });

    test('Scale.get with mixed cases', function () {
        expect(Scale::get('C lydian #5P PENTATONIC')->name)
            ->toBe(Scale::get('C lydian #5P pentatonic')->name)
            ->and(Scale::get('lydian #5P PENTATONIC')->name)
            ->toBe(Scale::get('lydian #5P pentatonic')->name);
    });

    test('intervals', function () {
        expect(Scale::get('major')->intervals)->toBe(explode(' ', '1P 2M 3M 4P 5P 6M 7M'))
            ->and(Scale::get('C major')->intervals)->toBe(explode(' ', '1P 2M 3M 4P 5P 6M 7M'))
            ->and(Scale::get('blah')->intervals)->toBe([]);
    });

    test('notes', function () {
        expect(Scale::get('C major')->notes)->toBe(explode(' ', 'C D E F G A B'))
            ->and(Scale::get('C lydian #9')->notes)->toBe(explode(' ', 'C D# E F# G A B'))
            ->and(Scale::get(['C', 'major'])->notes)->toBe(explode(' ', 'C D E F G A B'))
            ->and(Scale::get(['C4', 'major'])->notes)->toBe(explode(' ', 'C4 D4 E4 F4 G4 A4 B4'))
            ->and(Scale::get(['eb', 'bebop'])->notes)->toBe(explode(' ', 'Eb F G Ab Bb C Db D'))
            ->and(Scale::get(['C', 'no-scale'])->notes)->toBe([])
            ->and(Scale::get(['no-note', 'major'])->notes)->toBe([]);
    });

    describe('Scale.detect', function () {
        test('detect exact match', function () {
            expect(Scale::detect(['D', 'E', 'F#', 'A', 'B'], ['match' => 'exact']))
                ->toBe(['D major pentatonic'])
                ->and(Scale::detect(['D', 'E', 'F#', 'A', 'B'], ['match' => 'exact', 'tonic' => 'B']))
                ->toBe(['B minor pentatonic'])
                ->and(Scale::detect(['D', 'F#', 'B', 'C', 'C#'], ['match' => 'exact']))
                ->toBe([])
                ->and(Scale::detect(['c', 'd', 'e', 'f', 'g', 'a', 'b'], ['match' => 'exact']))
                ->toBe(['C major'])
                ->and(Scale::detect(['c2', 'd6', 'e3', 'f1', 'g7', 'a6', 'b5'], ['match' => 'exact', 'tonic' => 'd']))
                ->toBe(['D dorian']);

        });

        test('detect fit match', function () {
            expect(Scale::detect(['C', 'D', 'E', 'F', 'G', 'A', 'B'], ['match' => 'fit']))
                ->toBe(['C major', 'C bebop', 'C bebop major', 'C ichikosucho', 'C chromatic'])
                ->and(Scale::detect(['D', 'F#', 'B', 'C', 'C#'], ['match' => 'fit']))
                ->toBe(['D bebop', 'D kafi raga', 'D chromatic'])
                ->and(Scale::detect(['Ab', 'Bb', 'C', 'Db', 'Eb', 'G']))
                ->toBe(['Ab major', 'Ab bebop', 'Ab harmonic major', 'Ab bebop major', 'Ab ichikosucho', 'Ab chromatic']);

        });

        test('tonic will be added', function () {
            expect(Scale::detect(['c', 'd', 'e', 'f', 'g', 'b'], ['match' => 'exact']))
                ->toBe([])
                ->and(Scale::detect(['c', 'd', 'e', 'f', 'g', 'b'], ['match' => 'exact', 'tonic' => 'a']))
                ->toBe(['A minor']);

        });
    });

    test('Ukrainian Dorian scale', function () {
        expect(Scale::get('C romanian minor')->notes)->toBe(explode(' ', 'C D Eb F# G A Bb'))
            ->and(Scale::get('C ukrainian dorian')->notes)->toBe(explode(' ', 'C D Eb F# G A Bb'))
            ->and(Scale::get('B romanian minor')->notes)->toBe(explode(' ', 'B C# D E# F# G# A'))
            ->and(Scale::get('B dorian #4')->notes)->toBe(explode(' ', 'B C# D E# F# G# A'))
            ->and(Scale::get('B altered dorian')->notes)->toBe(explode(' ', 'B C# D E# F# G# A'));
    });

    test('chords: find all chords that fits into this scale', function () {
        expect(Scale::scaleChords('pentatonic'))->toBe(explode(' ', '5 M 6 sus2 Madd9'))
            ->and(Scale::scaleChords('none'))->toBe([]);
    });

    test('extended: find all scales that extends this one', function () {
        expect(Scale::extended('major'))->toBe(['bebop', 'bebop major', 'ichikosucho', 'chromatic'])
            ->and(Scale::extended('none'))->toBe([]);
    });

    test('Scale.reduced: all scales that are included in the given one', function () {
        expect(Scale::reduced('major'))->toBe(['major pentatonic', 'ionian pentatonic', 'ritusen'])
            ->and(Scale::reduced('D major'))->toBe(Scale::reduced('major'))
            ->and(Scale::reduced('none'))->toBe([]);
    });

    describe('specific and problematic scales', function () {
        test('whole note scale should use 6th', function () {
            expect(implode(' ', Scale::get('C whole tone')->notes))->toBe('C D E F# G# A#')
                ->and(implode(' ', Scale::get('Db whole tone')->notes))->toBe('Db Eb F G A B');
        });
    });

    test('scaleNotes', function () {
        expect(Scale::scaleNotes(explode(' ', 'C4 c3 C5 C4 c4')))->toBe(['C'])
            ->and(Scale::scaleNotes(explode(' ', 'C4 f3 c#10 b5 d4 cb4')))
            ->toBe(explode(' ', 'C C# D F B Cb'))
            ->and(Scale::scaleNotes(explode(' ', 'D4 c#5 A5 F#6')))
            ->toBe(['D', 'F#', 'A', 'C#']);
    });

    test('mode names', function () {
        expect(Scale::modeNames('pentatonic'))->toBe([
            ['1P', 'major pentatonic'],
            ['2M', 'egyptian'],
            ['3M', 'malkos raga'],
            ['5P', 'ritusen'],
            ['6M', 'minor pentatonic'],
        ])
            ->and(Scale::modeNames('whole tone pentatonic'))->toBe([
                ['1P', 'whole tone pentatonic'],
            ])
            ->and(Scale::modeNames('C pentatonic'))->toBe([
                ['C', 'major pentatonic'],
                ['D', 'egyptian'],
                ['E', 'malkos raga'],
                ['G', 'ritusen'],
                ['A', 'minor pentatonic'],
            ])
            ->and(Scale::modeNames('C whole tone pentatonic'))->toBe([
                ['C', 'whole tone pentatonic'],
            ]);

    });

    describe('rangeOf', function () {
        test('range of a scale name', function () {
            $range = Scale::rangeOf('C pentatonic');
            expect(implode(' ', $range('C4', 'C5')))->toBe('C4 D4 E4 G4 A4 C5')
                ->and(implode(' ', $range('C5', 'C4')))->toBe('C5 A4 G4 E4 D4 C4')
                ->and(implode(' ', $range('g3', 'a2')))->toBe('G3 E3 D3 C3 A2');
        });

        test('range of a scale name with flat', function () {
            $range = Scale::rangeOf('Cb major');
            expect(implode(' ', $range('Cb4', 'Cb5')))->toBe('Cb4 Db4 Eb4 Fb4 Gb4 Ab4 Bb4 Cb5');
        });

        test('range of a scale name with sharp', function () {
            $range = Scale::rangeOf('C# major');
            expect(implode(' ', $range('C#4', 'C#5')))->toBe('C#4 D#4 E#4 F#4 G#4 A#4 B#4 C#5');
        });

        test('range of a scale without tonic', function () {
            $range = Scale::rangeOf('pentatonic');
            expect($range('C4', 'C5'))->toBe([]);
        });

        test('range of a list of notes', function () {
            $range = Scale::rangeOf(['c4', 'g4', 'db3', 'g']);
            expect(implode(' ', $range('c4', 'c5')))->toBe('C4 Db4 G4 C5');
        });

        describe('degrees', function () {
            test('positive scale degrees', function () {
                expect(implode(' ', array_map(Scale::degrees('C major'), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])))
                    ->toBe('C D E F G A B C D E')
                    ->and(implode(' ', array_map(Scale::degrees('C4 major'), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])))
                    ->toBe('C4 D4 E4 F4 G4 A4 B4 C5 D5 E5')
                    ->and(implode(' ', array_map(Scale::degrees('C4 pentatonic'), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11])))
                    ->toBe('C4 D4 E4 G4 A4 C5 D5 E5 G5 A5 C6');

            });

            test('invalid inputs', function () {
                expect(Scale::degrees('C major')(0))->toBe('')
                    ->and(Scale::degrees('C nonsense')(0))->toBe('');
            });
        });

        test('negative scale degrees', function () {
            expect(implode(' ', array_map(Scale::degrees('C major'), [-1, -2, -3, -4, -5, -6, -7, -8, -9, -10])))
                ->toBe('B A G F E D C B A G')
                ->and(implode(' ', array_map(Scale::degrees('C4 major'), [-1, -2, -3, -4, -5, -6, -7, -8, -9, -10])))
                ->toBe('B3 A3 G3 F3 E3 D3 C3 B2 A2 G2')
                ->and(implode(' ', array_map(Scale::degrees('C4 pentatonic'), [-1, -2, -3, -4, -5, -6, -7, -8, -9, -10, -11])))
                ->toBe('A3 G3 E3 D3 C3 A2 G2 E2 D2 C2 A1');

        });
    });

    test('Scale.steps', function () {
        expect(array_map(Scale::steps('C4 major'), [-3, -2, -1, 0, 1, 2]))
            ->toBe(['G3', 'A3', 'B3', 'C4', 'D4', 'E4']);
    });
});
