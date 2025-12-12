<?php

declare(strict_types=1);

use Chaloman\Tonal\TimeSignature;

describe('TimeSignature', function () {

    test('get', function () {
        $ts = TimeSignature::get('4/4');

        expect($ts->empty)->toBeFalse();
        expect($ts->name)->toBe('4/4');
        expect($ts->type->value)->toBe('simple');
        expect($ts->upper)->toBe(4);
        expect($ts->lower)->toBe(4);
        expect($ts->additive)->toBe([]);
    });

    test('get invalid', function () {
        expect(TimeSignature::get('0/0')->empty)->toBeTrue();
    });

    test('simple', function () {
        expect(TimeSignature::get('4/4')->type->value)->toBe('simple');
        expect(TimeSignature::get('3/4')->type->value)->toBe('simple');
        expect(TimeSignature::get('2/4')->type->value)->toBe('simple');
        expect(TimeSignature::get('2/2')->type->value)->toBe('simple');
    });

    test('compound', function () {
        expect(TimeSignature::get('3/8')->type->value)->toBe('compound');
        expect(TimeSignature::get('6/8')->type->value)->toBe('compound');
        expect(TimeSignature::get('9/8')->type->value)->toBe('compound');
        expect(TimeSignature::get('12/8')->type->value)->toBe('compound');
    });

    test('irregular', function () {
        expect(TimeSignature::get('2+3+3/8')->type->value)->toBe('irregular');
        expect(TimeSignature::get('3+2+2/8')->type->value)->toBe('irregular');
    });

    test('irrational', function () {
        expect(TimeSignature::get('12/10')->type->value)->toBe('irrational');
        expect(TimeSignature::get('12/19')->type->value)->toBe('irrational');
    });

    test('names', function () {
        expect(TimeSignature::names())->toBe([
            '4/4',
            '3/4',
            '2/4',
            '2/2',
            '12/8',
            '9/8',
            '6/8',
            '3/8',
        ]);
    });

    test('additive time signatures', function () {
        $ts = TimeSignature::get('2+3+3/8');

        expect($ts->name)->toBe('2+3+3/8');
        expect($ts->upper)->toBe(8);
        expect($ts->additive)->toBe([2, 3, 3]);
    });

    test('array literal', function () {
        $ts = TimeSignature::get([4, 4]);

        expect($ts->name)->toBe('4/4');
        expect($ts->upper)->toBe(4);
        expect($ts->lower)->toBe(4);
    });

    test('caching returns same instance', function () {
        $ts1 = TimeSignature::get('4/4');
        $ts2 = TimeSignature::get('4/4');

        expect($ts1)->toBe($ts2);
    });
});
