<?php

declare(strict_types=1);

use Chaloman\Tonal\VoiceLeading;
use Chaloman\Tonal\Voicing;
use Chaloman\Tonal\VoicingDictionary;

describe('Voicing', function () {
    describe('search', function () {
        test('C major triad inversions', function () {
            expect(Voicing::search('C', ['C3', 'C5'], VoicingDictionary::triads()))
                ->toBe([
                    ['C3', 'E3', 'G3'],
                    ['C4', 'E4', 'G4'],
                    ['E3', 'G3', 'C4'],
                    ['E4', 'G4', 'C5'],
                    ['G3', 'C4', 'E4'],
                ]);
        });

        test('C^7 lefthand', function () {
            expect(Voicing::search('C^7', ['E3', 'D5'], VoicingDictionary::lefthand()))
                ->toBe([
                    ['E3', 'G3', 'B3', 'D4'],
                    ['E4', 'G4', 'B4', 'D5'],
                    ['B3', 'D4', 'E4', 'G4'],
                ]);
        });

        test('Cminor7 lefthand with custom dictionary', function () {
            expect(Voicing::search('Cminor7', ['Eb3', 'D5'], [
                'minor7' => ['3m 5P 7m 9M', '7m 9M 10m 12P'],
            ]))->toBe([
                ['Eb3', 'G3', 'Bb3', 'D4'],
                ['Eb4', 'G4', 'Bb4', 'D5'],
                ['Bb3', 'D4', 'Eb4', 'G4'],
            ]);
        });

        test('returns empty array for unknown chord', function () {
            expect(Voicing::search('Xunknown', ['C3', 'C5'], VoicingDictionary::triads()))
                ->toBe([]);
        });
    });

    describe('get', function () {
        test('get Dm7 with defaults', function () {
            expect(Voicing::get('Dm7'))
                ->toBe(['F3', 'A3', 'C4', 'E4']);
        });

        test('get Dm7 without lastVoicing', function () {
            expect(Voicing::get(
                'Dm7',
                ['F3', 'A4'],
                VoicingDictionary::lefthand(),
                [VoiceLeading::class, 'topNoteDiff'],
            ))->toBe(['F3', 'A3', 'C4', 'E4']);
        });

        test('get Dm7 with lastVoicing', function () {
            expect(Voicing::get(
                'Dm7',
                ['F3', 'A4'],
                VoicingDictionary::lefthand(),
                [VoiceLeading::class, 'topNoteDiff'],
                ['C4', 'E4', 'G4', 'B4'],
            ))->toBe(['C4', 'E4', 'F4', 'A4']);
        });

        test('get returns empty array for unknown chord', function () {
            expect(Voicing::get('Xunknown'))
                ->toBe([]);
        });
    });

    describe('sequence', function () {
        test('sequence C-F-G with triads', function () {
            expect(Voicing::sequence(
                ['C', 'F', 'G'],
                ['F3', 'A4'],
                VoicingDictionary::triads(),
                [VoiceLeading::class, 'topNoteDiff'],
            ))->toBe([
                ['C4', 'E4', 'G4'],    // root position
                ['A3', 'C4', 'F4'],    // first inversion (F4 closest to G4)
                ['B3', 'D4', 'G4'],    // first inversion (G4 closest to F4)
            ]);
        });

        test('sequence with initial lastVoicing', function () {
            $result = Voicing::sequence(
                ['Dm7'],
                ['F3', 'A4'],
                VoicingDictionary::lefthand(),
                [VoiceLeading::class, 'topNoteDiff'],
                ['C4', 'E4', 'G4', 'B4'],
            );

            expect($result[0])->toBe(['C4', 'E4', 'F4', 'A4']);
        });

        test('sequence handles empty chord array', function () {
            expect(Voicing::sequence([]))
                ->toBe([]);
        });
    });
});
