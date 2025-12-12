<?php

declare(strict_types=1);

namespace Chaloman\Tonal\Enums;

/**
 * Chord quality
 */
enum ChordQuality: string
{
    case Major = 'Major';
    case Minor = 'Minor';
    case Augmented = 'Augmented';
    case Diminished = 'Diminished';
    case Unknown = 'Unknown';
}
