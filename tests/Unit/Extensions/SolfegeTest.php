<?php

declare(strict_types=1);

use Chaloman\Tonal\Extensions\Solfege\Solfege;
use Chaloman\Tonal\Note;

describe('Solfege', function () {
    describe('toEnglish', function () {
        test('converts basic solfege notes to english', function () {
            expect(Solfege::toEnglish('Do'))->toBe('C')
                ->and(Solfege::toEnglish('Re'))->toBe('D')
                ->and(Solfege::toEnglish('Mi'))->toBe('E')
                ->and(Solfege::toEnglish('Fa'))->toBe('F')
                ->and(Solfege::toEnglish('Sol'))->toBe('G')
                ->and(Solfege::toEnglish('La'))->toBe('A')
                ->and(Solfege::toEnglish('Si'))->toBe('B')
                ->and(Solfege::toEnglish('Ti'))->toBe('B');
        });

        test('converts notes with octave', function () {
            expect(Solfege::toEnglish('Do4'))->toBe('C4')
                ->and(Solfege::toEnglish('Re3'))->toBe('D3')
                ->and(Solfege::toEnglish('Sol5'))->toBe('G5')
                ->and(Solfege::toEnglish('La0'))->toBe('A0');
        });

        test('converts notes with accidentals', function () {
            expect(Solfege::toEnglish('Do#'))->toBe('C#')
                ->and(Solfege::toEnglish('Re#4'))->toBe('D#4')
                ->and(Solfege::toEnglish('Solb'))->toBe('Gb')
                ->and(Solfege::toEnglish('Lab3'))->toBe('Ab3')
                ->and(Solfege::toEnglish('Fa#5'))->toBe('F#5');
        });

        test('converts notes with unicode symbols', function () {
            expect(Solfege::toEnglish('Do♯4'))->toBe('C#4')
                ->and(Solfege::toEnglish('Mi♭3'))->toBe('Eb3')
                ->and(Solfege::toEnglish('Fa♯'))->toBe('F#')
                ->and(Solfege::toEnglish('La♭'))->toBe('Ab');
        });

        test('is case insensitive', function () {
            expect(Solfege::toEnglish('do'))->toBe('C')
                ->and(Solfege::toEnglish('DO'))->toBe('C')
                ->and(Solfege::toEnglish('Do'))->toBe('C')
                ->and(Solfege::toEnglish('sol4'))->toBe('G4')
                ->and(Solfege::toEnglish('SOL4'))->toBe('G4');
        });

        test('returns unchanged if already in english', function () {
            expect(Solfege::toEnglish('C4'))->toBe('C4')
                ->and(Solfege::toEnglish('D#3'))->toBe('D#3')
                ->and(Solfege::toEnglish('Gb5'))->toBe('Gb5')
                ->and(Solfege::toEnglish('A'))->toBe('A');
        });

        test('does NOT convert false positives - words starting with note names', function () {
            expect(Solfege::toEnglish('regular'))->toBe('regular')
                ->and(Solfege::toEnglish('reunion'))->toBe('reunion')
                ->and(Solfege::toEnglish('Dominio'))->toBe('Dominio')
                ->and(Solfege::toEnglish('solamente'))->toBe('solamente')
                ->and(Solfege::toEnglish('familiar'))->toBe('familiar')
                ->and(Solfege::toEnglish('similar'))->toBe('similar')
                ->and(Solfege::toEnglish('laboratorio'))->toBe('laboratorio')
                ->and(Solfege::toEnglish('domingo'))->toBe('domingo')
                ->and(Solfege::toEnglish('miedo'))->toBe('miedo')
                ->and(Solfege::toEnglish('factor'))->toBe('factor')
                ->and(Solfege::toEnglish('silencio'))->toBe('silencio')
                ->and(Solfege::toEnglish('tiempo'))->toBe('tiempo')
                ->and(Solfege::toEnglish('Colador'))->toBe('Colador')
                ->and(Solfege::toEnglish('Girasol'))->toBe('Girasol');
        });
    });

    describe('toSolfege', function () {
        test('converts basic english notes to solfege', function () {
            expect(Solfege::toSolfege('C'))->toBe('Do')
                ->and(Solfege::toSolfege('D'))->toBe('Re')
                ->and(Solfege::toSolfege('E'))->toBe('Mi')
                ->and(Solfege::toSolfege('F'))->toBe('Fa')
                ->and(Solfege::toSolfege('G'))->toBe('Sol')
                ->and(Solfege::toSolfege('A'))->toBe('La')
                ->and(Solfege::toSolfege('B'))->toBe('Si');
        });

        test('converts notes with octave', function () {
            expect(Solfege::toSolfege('C4'))->toBe('Do4')
                ->and(Solfege::toSolfege('D3'))->toBe('Re3')
                ->and(Solfege::toSolfege('G5'))->toBe('Sol5')
                ->and(Solfege::toSolfege('A0'))->toBe('La0');
        });

        test('converts notes with accidentals', function () {
            expect(Solfege::toSolfege('C#'))->toBe('Do#')
                ->and(Solfege::toSolfege('D#4'))->toBe('Re#4')
                ->and(Solfege::toSolfege('Gb'))->toBe('Solb')
                ->and(Solfege::toSolfege('Ab3'))->toBe('Lab3')
                ->and(Solfege::toSolfege('F#5'))->toBe('Fa#5');
        });

        test('converts notes with unicode symbols', function () {
            expect(Solfege::toSolfege('C♯4'))->toBe('Do#4')
                ->and(Solfege::toSolfege('E♭3'))->toBe('Mib3')
                ->and(Solfege::toSolfege('F♯'))->toBe('Fa#')
                ->and(Solfege::toSolfege('A♭'))->toBe('Lab');
        });

        test('returns unchanged if already in solfege', function () {
            expect(Solfege::toSolfege('Do4'))->toBe('Do4')
                ->and(Solfege::toSolfege('Re#3'))->toBe('Re#3')
                ->and(Solfege::toSolfege('Solb5'))->toBe('Solb5');
        });
    });

    describe('isSolfege', function () {
        test('detects solfege notes', function () {
            expect(Solfege::isSolfege('Do'))->toBeTrue()
                ->and(Solfege::isSolfege('Do4'))->toBeTrue()
                ->and(Solfege::isSolfege('Re#'))->toBeTrue()
                ->and(Solfege::isSolfege('Solb3'))->toBeTrue()
                ->and(Solfege::isSolfege('Ti'))->toBeTrue();
        });

        test('returns false for english notes', function () {
            expect(Solfege::isSolfege('C'))->toBeFalse()
                ->and(Solfege::isSolfege('C4'))->toBeFalse()
                ->and(Solfege::isSolfege('D#'))->toBeFalse()
                ->and(Solfege::isSolfege('Gb3'))->toBeFalse();
        });

        test('returns false for words that are not notes', function () {
            expect(Solfege::isSolfege('regular'))->toBeFalse()
                ->and(Solfege::isSolfege('Dominio'))->toBeFalse()
                ->and(Solfege::isSolfege('solamente'))->toBeFalse()
                ->and(Solfege::isSolfege('familiar'))->toBeFalse();
        });
    });

    describe('note', function () {
        test('creates PitchNote from solfege notation', function () {
            $note = Solfege::note('Do4');

            expect($note->name)->toBe('C4')
                ->and($note->midi)->toBe(60)
                ->and($note->empty)->toBeFalse();
        });

        test('creates notes with accidentals', function () {
            expect(Solfege::note('Do#4')->midi)->toBe(61)
                ->and(Solfege::note('Reb4')->midi)->toBe(61)
                ->and(Solfege::note('Sol4')->midi)->toBe(67);
        });

        test('notes are compatible with core', function () {
            $doNote = Solfege::note('Do4');
            $cNote = Note::get('C4');

            expect($doNote->midi)->toBe($cNote->midi)
                ->and($doNote->freq)->toBe($cNote->freq)
                ->and($doNote->chroma)->toBe($cNote->chroma);
        });
    });

    describe('name', function () {
        test('gets solfege name from core note', function () {
            expect(Solfege::name(Note::get('C4')))->toBe('Do4')
                ->and(Solfege::name(Note::get('D#3')))->toBe('Re#3')
                ->and(Solfege::name(Note::get('Gb5')))->toBe('Solb5')
                ->and(Solfege::name(Note::get('A')))->toBe('La');
        });

        test('roundtrip: note -> name maintains equivalence', function () {
            $original = 'Sol#4';
            $note = Solfege::note($original);
            $back = Solfege::name($note);

            expect($back)->toBe($original);
        });
    });

    describe('core integration', function () {
        test('transposition with solfege output', function () {
            $note = Solfege::note('Do4');
            $transposed = Note::transpose($note->name, '5P');

            expect(Solfege::toSolfege($transposed))->toBe('Sol4');
        });

        test('interoperability with core functions', function () {
            $do = Solfege::note('Do4');
            $sol = Solfege::note('Sol4');

            // Notes work with all core functions
            expect(Note::midi($do->name))->toBe(60)
                ->and(Note::midi($sol->name))->toBe(67);
        });
    });
});
