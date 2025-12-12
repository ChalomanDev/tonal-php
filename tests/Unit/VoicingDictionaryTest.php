<?php

declare(strict_types=1);

use Chaloman\Tonal\VoicingDictionary;

describe('VoicingDictionary', function () {
    describe('lookup', function () {
        test('lookup M in triads', function () {
            expect(VoicingDictionary::lookup('M', VoicingDictionary::triads()))
                ->toBe(['1P 3M 5P', '3M 5P 8P', '5P 8P 10M']);
        });

        test('lookup empty string (major) in triads via alias', function () {
            expect(VoicingDictionary::lookup('', VoicingDictionary::triads()))
                ->toBe(['1P 3M 5P', '3M 5P 8P', '5P 8P 10M']);
        });

        test('lookup custom dictionary', function () {
            expect(VoicingDictionary::lookup('minor', ['minor' => ['1P 3m 5P']]))
                ->toBe(['1P 3m 5P']);
        });

        test('lookup in lefthand dictionary', function () {
            expect(VoicingDictionary::lookup('m7', VoicingDictionary::lefthand()))
                ->toBe(['3m 5P 7m 9M', '7m 9M 10m 12P']);
        });

        test('lookup ^7 in lefthand dictionary', function () {
            expect(VoicingDictionary::lookup('^7', VoicingDictionary::lefthand()))
                ->toBe(['3M 5P 7M 9M', '7M 9M 10M 12P']);
        });

        test('lookup returns null for unknown symbol', function () {
            expect(VoicingDictionary::lookup('unknown', VoicingDictionary::triads()))
                ->toBeNull();
        });
    });

    describe('dictionaries', function () {
        test('triads returns triads dictionary', function () {
            $triads = VoicingDictionary::triads();
            expect($triads)->toHaveKeys(['M', 'm', 'o', 'aug']);
        });

        test('lefthand returns lefthand dictionary', function () {
            $lefthand = VoicingDictionary::lefthand();
            expect($lefthand)->toHaveKeys(['m7', '7', '^7', 'm7b5']);
        });

        test('all returns combined dictionary', function () {
            $all = VoicingDictionary::all();
            expect($all)->toHaveKeys(['M', 'm', 'm7', '7', '^7']);
        });

        test('defaultDictionary returns lefthand', function () {
            expect(VoicingDictionary::defaultDictionary())
                ->toBe(VoicingDictionary::lefthand());
        });
    });
});
