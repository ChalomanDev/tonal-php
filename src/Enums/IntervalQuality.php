<?php

declare(strict_types=1);

namespace Chaloman\Tonal\Enums;

/**
 * Interval quality
 */
enum IntervalQuality: string
{
    case Diminished4 = 'dddd';
    case Diminished3 = 'ddd';
    case Diminished2 = 'dd';
    case Diminished = 'd';
    case Minor = 'm';
    case Major = 'M';
    case Perfect = 'P';
    case Augmented = 'A';
    case Augmented2 = 'AA';
    case Augmented3 = 'AAA';
    case Augmented4 = 'AAAA';
}
