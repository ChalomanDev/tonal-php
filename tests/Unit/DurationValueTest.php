<?php

declare(strict_types=1);

use Chaloman\Tonal\DurationValue;

describe('@tonaljs/duration-value', function () {

    test('get shorthand', function () {
        $result = DurationValue::get('q');

        expect($result->empty)->toBeFalse();
        expect($result->name)->toBe('q');
        expect($result->value)->toBe(0.25);
        expect($result->fraction)->toBe([1, 4]);
        expect($result->dots)->toBe('');
        expect($result->shorthand)->toBe('q');
        expect($result->names)->toBe(['quarter', 'crotchet']);
    });

    test('get with dot', function () {
        $result = DurationValue::get('dl.');

        expect($result->empty)->toBeFalse();
        expect($result->name)->toBe('dl.');
        expect($result->dots)->toBe('.');
        expect($result->value)->toBe(12.0);
        expect($result->fraction)->toBe([12, 1]);
        expect($result->shorthand)->toBe('dl');
        expect($result->names)->toBe(['large', 'duplex longa', 'maxima', 'octuple', 'octuple whole']);
    });

    test('get long name', function () {
        $result = DurationValue::get('large.');

        expect($result->empty)->toBeFalse();
        expect($result->name)->toBe('large.');
    });

    test('value for duplex longa', function () {
        $expected = [8.0, 12.0, 14.0, 15.0];
        $actual = array_map(fn($n) => DurationValue::value($n), ['dl', 'dl.', 'dl..', 'dl...']);

        expect($actual)->toBe($expected);
    });

    test('value for longa', function () {
        $expected = [4.0, 6.0, 7.0, 7.5];
        $actual = array_map(fn($n) => DurationValue::value($n), ['l', 'l.', 'l..', 'l...']);

        expect($actual)->toBe($expected);
    });

    test('value for quarter', function () {
        $expected = [0.25, 0.375, 0.4375, 0.46875];
        $actual = array_map(fn($n) => DurationValue::value($n), ['q', 'q.', 'q..', 'q...']);

        expect($actual)->toBe($expected);
    });

    test('fraction for whole note', function () {
        $expected = [[1, 1], [3, 2], [7, 4], [15, 8]];
        $actual = array_map(fn($n) => DurationValue::fraction($n), ['w', 'w.', 'w..', 'w...']);

        expect($actual)->toBe($expected);
    });

    test('shorthands', function () {
        $result = implode(',', DurationValue::shorthands());

        expect($result)->toBe('dl,l,d,w,h,q,e,s,t,sf,h,th');
    });

    test('names', function () {
        $result = implode(',', DurationValue::names());

        expect($result)->toBe(
            'large,duplex longa,maxima,octuple,octuple whole,long,longa,double whole,double,breve,whole,semibreve,half,minim,quarter,crotchet,eighth,quaver,sixteenth,semiquaver,thirty-second,demisemiquaver,sixty-fourth,hemidemisemiquaver,hundred twenty-eighth,two hundred fifty-sixth'
        );
    });

    test('invalid name returns empty', function () {
        $result = DurationValue::get('invalid');

        expect($result->empty)->toBeTrue();
        expect($result->value)->toBe(0.0);
    });
});
