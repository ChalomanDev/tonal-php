<?php

declare(strict_types=1);

// Preset PHP: evita die, var_dump, funciones deprecadas
// Ignora archivos de datos que contienen símbolos musicales (Δ para maj7)
arch()->preset()->php()
    ->ignoring('Chaloman\Tonal\Data');

// Preset seguridad: evita eval, md5, etc.
// Ignora mt_rand en Collection::shuffle (necesario para compatibilidad con Tonal.js)
arch()->preset()->security()
    ->ignoring('mt_rand');

// No usar funciones de debug
arch()
    ->expect('Chaloman\Tonal')
    ->not->toUse(['dd', 'dump', 'ray']);
