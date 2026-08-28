# Changelog


## [1.3.0] - 2026-08-28

### Added

- DetectorSource ;
- SeparatedValuesDetector ;
- CSV/TSV structural detection ;
- Integration tests ;
- GitHub Actions CI.

### Changed

- Rename Detector API/classes ;
- Rename `EnumMimeType` Enum to `MimeType` ;
- Rename `FileInspector` to `FileResource` ;
- Rename `MimeTypeException` to `MimeDetectorException` ;
- `DetectionResult` now exposes `DetectorSource` Enum ;
- CSV/TSV detection moved out of `TextDetector` to new detector `SeparatedValuesDetector`.


## [1.2.0] - 2026-08-27

### Added

* Add `DiskImageDetector` for `ISO 9660` and `Apple Disk Image` detection.
* Add `FileInspector` filesize inspection with cached resolution.
* Add hexadecimal header and offset-based file reading helpers through `readHex()`.
* Extend `DetectionResult` with file size and path metadata: `dirname()`, `basename()`, `filename()`, `extension()` and `pathInfo()`.

### Changed

* Refactor detectors to create `DetectionResult` through `DetectionResult::create()`.
* Refactor `FileInspector` to validate files through the static `create()` factory and preload the file header.


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
