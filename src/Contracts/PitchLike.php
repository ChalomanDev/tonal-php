<?php

declare(strict_types=1);

namespace Chaloman\Tonal\Contracts;

/**
 * Interface for objects that can be converted to a Pitch
 * Used by RomanNumeral and similar objects that have pitch properties
 */
interface PitchLike
{
    public function getStep(): int;

    public function getAlt(): int;

    public function getOct(): int;

    public function getDir(): int;
}
