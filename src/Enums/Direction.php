<?php

declare(strict_types=1);

namespace Chaloman\Tonal\Enums;

/**
 * Direction type for intervals: 1 = ascending, -1 = descending
 */
enum Direction: int
{
    case Ascending = 1;
    case Descending = -1;
}
