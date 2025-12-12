# Tonal PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chaloman/tonal-php.svg?style=flat-square)](https://packagist.org/packages/chaloman/tonal-php)
[![Total Downloads](https://img.shields.io/packagist/dt/chaloman/tonal-php.svg?style=flat-square)](https://packagist.org/packages/chaloman/tonal-php)
[![License](https://img.shields.io/packagist/l/chaloman/tonal-php.svg?style=flat-square)](https://packagist.org/packages/chaloman/tonal-php)

A PHP port of [Tonal.js](https://github.com/tonaljs/tonal) - a music theory library for working with notes, intervals, chords, scales, modes, keys, and more.

## Requirements

- PHP 8.3 or higher

## Installation

```bash
composer require chaloman/tonal-php
```

## Usage

### Notes

```php
use Chaloman\Tonal\Note;

// Get note properties
$note = Note::get('C#4');
$note->name;    // "C#4"
$note->pc;      // "C#" (pitch class)
$note->letter;  // "C"
$note->acc;     // "#"
$note->octave;  // 4
$note->midi;    // 61
$note->freq;    // 277.18...

// Transpose notes
Note::transpose('C4', '3M');  // "E4"
Note::transpose('D4', '-2M'); // "C4"

// Get enharmonic equivalents
Note::enharmonic('C#');  // "Db"
Note::enharmonic('Db');  // "C#"

// Simplify notes
Note::simplify('E##'); // "F#"
```

### Intervals

```php
use Chaloman\Tonal\Interval;

// Get interval properties
$interval = Interval::get('3M');
$interval->name;     // "3M"
$interval->semitones; // 4
$interval->quality;  // "M"
$interval->num;      // 3

// Calculate distance between notes
Interval::distance('C4', 'E4'); // "3M"

// Add intervals
Interval::add('3M', '3m'); // "5P"

// Simplify compound intervals
Interval::simplify('9M'); // "2M"

// Invert intervals
Interval::invert('3M'); // "6m"
```

### Chords

```php
use Chaloman\Tonal\Chord;

// Get chord properties
$chord = Chord::get('Cmaj7');
$chord->name;      // "C major seventh"
$chord->symbol;    // "Cmaj7"
$chord->tonic;     // "C"
$chord->type;      // "major seventh"
$chord->notes;     // ["C", "E", "G", "B"]
$chord->intervals; // ["1P", "3M", "5P", "7M"]

// Get chord notes with octave
Chord::get('Dm7/F')->notes; // ["F", "A", "C", "D"]

// Transpose chords
Chord::transpose('Cmaj7', '5P'); // "Gmaj7"

// Detect chords from notes
use Chaloman\Tonal\ChordDetect;
ChordDetect::detect(['C', 'E', 'G']);     // ["CM", "Em#5/C", ...]
ChordDetect::detect(['D', 'F', 'A', 'C']); // ["Dm7", ...]
```

### Scales

```php
use Chaloman\Tonal\Scale;

// Get scale properties
$scale = Scale::get('C major');
$scale->name;      // "C major"
$scale->tonic;     // "C"
$scale->type;      // "major"
$scale->notes;     // ["C", "D", "E", "F", "G", "A", "B"]
$scale->intervals; // ["1P", "2M", "3M", "4P", "5P", "6M", "7M"]

// Get scale notes
Scale::get('D dorian')->notes; // ["D", "E", "F", "G", "A", "B", "C"]

// Detect scales from notes
Scale::detect(['C', 'D', 'E', 'F', 'G', 'A', 'B']); // ["C major", "C ionian", ...]

// Find chords in a scale
Scale::chords('C major'); // ["C", "Dm", "Em", "F", "G", "Am", "Bdim"]
```

### Keys

```php
use Chaloman\Tonal\Key;

// Major keys
$key = Key::majorKey('C');
$key->tonic;       // "C"
$key->type;        // "major"
$key->scale;       // ["C", "D", "E", "F", "G", "A", "B"]
$key->chords;      // ["C", "Dm", "Em", "F", "G", "Am", "Bdim"]

// Minor keys
$key = Key::minorKey('A');
$key->natural->scale;  // ["A", "B", "C", "D", "E", "F", "G"]
$key->harmonic->scale; // ["A", "B", "C", "D", "E", "F", "G#"]
$key->melodic->scale;  // ["A", "B", "C", "D", "E", "F#", "G#"]
```

### Modes

```php
use Chaloman\Tonal\Mode;

// Get mode properties
$mode = Mode::get('dorian');
$mode->name;      // "dorian"
$mode->intervals; // ["1P", "2M", "3m", "4P", "5P", "6M", "7m"]
$mode->triad;     // "m"
$mode->seventh;   // "m7"

// Get notes of a mode
Mode::notes('dorian', 'D'); // ["D", "E", "F", "G", "A", "B", "C"]

// Get all mode names
Mode::names(); // ["ionian", "dorian", "phrygian", ...]
```

### Progressions

```php
use Chaloman\Tonal\Progression;

// Convert roman numerals to chords
Progression::fromRomanNumerals('C', ['I', 'IV', 'V', 'I']);
// ["C", "F", "G", "C"]

// Convert chords to roman numerals
Progression::toRomanNumerals('C', ['C', 'Dm', 'G', 'C']);
// ["I", "IIm", "V", "I"]
```

### MIDI

```php
use Chaloman\Tonal\Midi;

// Convert between MIDI and notes
Midi::toMidi('C4');       // 60
Midi::midiToNoteName(60); // "C4"

// Convert between MIDI and frequency
Midi::midiToFreq(69);  // 440.0
Midi::freqToMidi(440); // 69
```

### Roman Numerals

```php
use Chaloman\Tonal\RomanNumeral;

$rn = RomanNumeral::get('bVII');
$rn->name;     // "bVII"
$rn->roman;    // "VII"
$rn->interval; // "7m"
$rn->acc;      // "b"
$rn->major;    // true
```

## Available Modules

- **Note** - Note operations (transpose, enharmonic, simplify)
- **Interval** - Interval calculations (add, subtract, invert)
- **Chord** - Chord properties and operations
- **ChordType** - Chord type dictionary
- **ChordDetect** - Chord detection from notes
- **Scale** - Scale properties and operations
- **ScaleType** - Scale type dictionary
- **Key** - Major and minor key information
- **Mode** - Musical modes
- **Pcset** - Pitch class set operations
- **Progression** - Roman numeral progressions
- **RomanNumeral** - Roman numeral analysis
- **Midi** - MIDI utilities
- **Range** - Note range generation
- **Collection** - Array utilities
- **AbcNotation** - ABC notation conversion
- **DurationValue** - Note duration values
- **TimeSignature** - Time signature parsing
- **Voicing** - Chord voicing utilities
- **VoicingDictionary** - Voicing patterns
- **VoiceLeading** - Voice leading algorithms
- **RhythmPattern** - Rhythm pattern generation

## Credits

This library is a PHP port of [Tonal.js](https://github.com/tonaljs/tonal) by [danigb](https://github.com/danigb).

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
