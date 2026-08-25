# Changelog


## [1.1.1] - 2026-08-25

### Fixed

- Rename call from `riffDetector::detect($inspector)` to `RiffDetector::detect($inspector)`.


## [1.1.0] - 2026-08-12

### Changed

- Reworked MIME type detection pipeline to prioritize lightweight magic-number detection before ZIP inspection, text analysis, and `fileinfo/libmagic`.
- Replaced `exif_imagetype()` with direct image signature detection for supported image formats, reducing unnecessary file inspection overhead.
- Improved MIME type detection performance for large files by avoiding expensive detectors when the file signature already identifies the MIME type.
- Added direct magic-number detection for GIF, JPEG, PNG, BMP, TIFF, and ICO images.


## [1.0.0] - 2026-08-06

### Added

- MIME detection by magic numbers
- ZIP container inspection
- Office document detection
- OpenDocument detection
- EPUB detection
- APK detection
- JAR detection
- ISO Base Media detection
- RIFF detection
- Text format detection
- PHP FileInfo fallback
