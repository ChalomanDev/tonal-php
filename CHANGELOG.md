# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-12-12

### Added

- Initial release - PHP port of Tonal.js
- **Core modules:**
  - Note - Note parsing and operations
  - Interval - Interval calculations
  - Pitch - Base pitch utilities
  - PitchNote - Note pitch operations
  - PitchInterval - Interval pitch operations
  - PitchDistance - Distance calculations between pitches
- **Chord modules:**
  - Chord - Chord properties and operations
  - ChordType - Chord type dictionary with 100+ chord types
  - ChordDetect - Chord detection from note arrays
- **Scale modules:**
  - Scale - Scale properties and operations
  - ScaleType - Scale type dictionary with 80+ scale types
- **Key and Mode modules:**
  - Key - Major and minor key information
  - Mode - Musical modes (ionian, dorian, etc.)
- **Utility modules:**
  - Pcset - Pitch class set operations
  - Progression - Roman numeral chord progressions
  - RomanNumeral - Roman numeral analysis
  - Midi - MIDI number utilities
  - Range - Note range generation
  - Collection - Array utilities
- **Additional modules:**
  - AbcNotation - ABC notation conversion
  - DurationValue - Note duration values
  - TimeSignature - Time signature parsing
  - Voicing - Chord voicing utilities
  - VoicingDictionary - Voicing patterns dictionary
  - VoiceLeading - Voice leading algorithms
  - RhythmPattern - Rhythm pattern generation
- Full test coverage with 349 tests

[Unreleased]: https://github.com/ChalomanDev/tonal-php/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/ChalomanDev/tonal-php/releases/tag/v1.0.0
