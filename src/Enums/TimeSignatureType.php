<?php

declare(strict_types=1);

namespace Chaloman\Tonal\Enums;

/**
 * Time signature type
 */
enum TimeSignatureType: string
{
    case Simple = 'simple';
    case Compound = 'compound';
    case Irregular = 'irregular';
    case Irrational = 'irrational';
}
