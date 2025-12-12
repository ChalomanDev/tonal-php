<?php

declare(strict_types=1);

use Chaloman\Tonal\Note;

describe('Note', function () {
    test('get', function () {
        $note = Note::get('C4');

        expect($note->acc)->toBe('')
            ->and($note->alt)->toBe(0)
            ->and($note->chroma)->toBe(0)
            ->and($note->coord)->toBe([0, 4])
            ->and($note->empty)->toBeFalse()
            ->and($note->freq)->toBe(261.6255653005986)
            ->and($note->height)->toBe(60)
            ->and($note->letter)->toBe('C')
            ->and($note->midi)->toBe(60)
            ->and($note->name)->toBe('C4')
            ->and($note->oct)->toBe(4)
            ->and($note->pc)->toBe('C')
            ->and($note->step)->toBe(0);
    });

    test('property shorthands', function () {
        expect(Note::name('db'))->toBe('Db')
            ->and(Note::pitchClass('Ax4'))->toBe('A##')
            ->and(Note::chroma('db4'))->toBe(1)
            ->and(Note::midi('db4'))->toBe(61)
            ->and(Note::freq('A4'))->toBe(440.0);
    });

    test('simplify', function () {
        expect(Note::simplify('C#'))->toBe('C#')
            ->and(Note::simplify('C##'))->toBe('D')
            ->and(Note::simplify('C###'))->toBe('D#')
            ->and(Note::simplify('B#4'))->toBe('C5');

        $notes = explode(' ', 'C## C### F##4 Gbbb5 B#4 Cbb4');
        expect(array_map(Note::simplify(...), $notes))
            ->toBe(explode(' ', 'D D# G4 E5 C5 Bb3'))
            ->and(Note::simplify('x'))->toBe('');

    });

    test('from midi', function () {
        expect(Note::fromMidi(70))->toBe('Bb4')
            ->and(array_map(Note::fromMidi(...), [60, 61, 62]))->toBe(['C4', 'Db4', 'D4'])
            ->and(array_map(Note::fromMidiSharps(...), [60, 61, 62]))->toBe(['C4', 'C#4', 'D4']);
    });

    test('names', function () {
        expect(Note::names())->toBe(['C', 'D', 'E', 'F', 'G', 'A', 'B'])
            ->and(Note::names(['fx', 'bb', 12, 'nothing', [], null]))->toBe(['F##', 'Bb']);
    });

    test('sortedNames', function () {
        expect(Note::sortedNames(explode(' ', 'c f g a b h j')))->toBe(explode(' ', 'C F G A B'))
            ->and(Note::sortedNames(explode(' ', 'c f g a b h j j h b a g f c')))
            ->toBe(explode(' ', 'C C F F G G A A B B'))
            ->and(Note::sortedNames(explode(' ', 'c2 c5 c1 c0 c6 c')))
            ->toBe(explode(' ', 'C C0 C1 C2 C5 C6'))
            ->and(Note::sortedNames(explode(' ', 'c2 c5 c1 c0 c6 c'), Note::descending()))
            ->toBe(explode(' ', 'C6 C5 C2 C1 C0 C'));
    });

    test('sortedUniq', function () {
        expect(Note::sortedUniqNames(explode(' ', 'a b c2 1p p2 c2 b c c3')))
            ->toBe(explode(' ', 'C A B C2 C3'));
    });

    test('transpose', function () {
        expect(Note::transpose('A4', '3M'))->toBe('C#5')
            ->and(Note::tr('A4', '3M'))->toBe('C#5');
    });

    test('transposeFrom', function () {
        $fromC4 = Note::transposeFrom('C4');
        expect($fromC4('5P'))->toBe('G4');

        $fromC = Note::transposeFrom('C');
        expect(array_map($fromC, ['1P', '3M', '5P']))->toBe(['C', 'E', 'G']);
    });

    test('transposeBy', function () {
        $by5P = Note::transposeBy('5P');
        expect($by5P('C4'))->toBe('G4')
            ->and(array_map(Note::transposeBy('5P'), ['C', 'D', 'E']))->toBe(['G', 'A', 'B']);
    });

    test('enharmonic', function () {
        expect(Note::enharmonic('C#'))->toBe('Db')
            ->and(Note::enharmonic('C##'))->toBe('D')
            ->and(Note::enharmonic('C###'))->toBe('Eb')
            ->and(Note::enharmonic('B#4'))->toBe('C5');

        $notes = explode(' ', 'C## C### F##4 Gbbb5 B#4 Cbb4');
        expect(array_map(fn ($n) => Note::enharmonic($n), $notes))
            ->toBe(explode(' ', 'D Eb G4 E5 C5 A#3'))
            ->and(Note::enharmonic('x'))->toBe('')
            ->and(Note::enharmonic('F2', 'E#'))->toBe('E#2')
            ->and(Note::enharmonic('B2', 'Cb'))->toBe('Cb3')
            ->and(Note::enharmonic('C2', 'B#'))->toBe('B#1')
            ->and(Note::enharmonic('F2', 'Eb'))->toBe('');
    });

    test('transposeFifths', function () {
        expect(Note::transposeFifths('G4', 3))->toBe('E6')
            ->and(Note::transposeFifths('G', 3))->toBe('E');

        $ns = array_map(fn ($n) => Note::transposeFifths('C2', $n), [0, 1, 2, 3, 4, 5]);
        expect($ns)->toBe(['C2', 'G2', 'D3', 'A3', 'E4', 'B4']);

        $sharps = array_map(fn ($n) => Note::transposeFifths('F#', $n), [0, 1, 2, 3, 4, 5, 6]);
        expect($sharps)->toBe(['F#', 'C#', 'G#', 'D#', 'A#', 'E#', 'B#']);

        $flats = array_map(fn ($n) => Note::transposeFifths('Bb', $n), [0, -1, -2, -3, -4, -5, -6]);
        expect($flats)->toBe(['Bb', 'Eb', 'Ab', 'Db', 'Gb', 'Cb', 'Fb']);
    });

    test('transposeOctaves', function () {
        $up = array_map(fn ($oct) => Note::transposeOctaves('C4', $oct), [0, 1, 2, 3, 4]);
        expect(implode(' ', $up))->toBe('C4 C5 C6 C7 C8');

        $down = array_map(fn ($oct) => Note::transposeOctaves('C4', $oct), [-1, -2, -3, -4, -5]);
        expect(implode(' ', $down))->toBe('C3 C2 C1 C0 C-1');
    });

    test('fromFreq', function () {
        expect(Note::fromFreq(440))->toBe('A4')
            ->and(Note::fromFreq(444))->toBe('A4')
            ->and(Note::fromFreq(470))->toBe('Bb4')
            ->and(Note::fromFreqSharps(470))->toBe('A#4')
            ->and(Note::fromFreq(0))->toBe('')
            ->and(Note::fromFreq(NAN))->toBe('');
    });
});
