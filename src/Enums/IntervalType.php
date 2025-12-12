<?php

declare(strict_types=1);

namespace Chaloman\Tonal\Enums;

/**
 * Interval type
 */
enum IntervalType: string
{
    case Perfectable = 'perfectable';
    case Majorable = 'majorable';
}
