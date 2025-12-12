<?php

declare(strict_types=1);

use Chaloman\Tonal\VoiceLeading;

describe('VoiceLeading', function () {
    test('topNoteDiff selects voicing with closest top note', function () {
        $voicings = [
            ['F3', 'A3', 'C4', 'E4'],
            ['C4', 'E4', 'F4', 'A4'],
        ];
        $lastVoicing = ['C4', 'E4', 'G4', 'B4'];

        expect(VoiceLeading::topNoteDiff($voicings, $lastVoicing))
            ->toBe(['C4', 'E4', 'F4', 'A4']);
    });

    test('topNoteDiff returns first voicing when lastVoicing is empty', function () {
        $voicings = [
            ['F3', 'A3', 'C4', 'E4'],
            ['C4', 'E4', 'F4', 'A4'],
        ];

        expect(VoiceLeading::topNoteDiff($voicings, []))
            ->toBe(['F3', 'A3', 'C4', 'E4']);
    });

    test('topNoteDiff returns empty array when voicings is empty', function () {
        expect(VoiceLeading::topNoteDiff([], ['C4', 'E4', 'G4']))
            ->toBe([]);
    });

    test('topNoteDiff with single voicing', function () {
        $voicings = [['E3', 'G3', 'B3', 'D4']];
        $lastVoicing = ['C4', 'E4', 'G4', 'B4'];

        expect(VoiceLeading::topNoteDiff($voicings, $lastVoicing))
            ->toBe(['E3', 'G3', 'B3', 'D4']);
    });
});
