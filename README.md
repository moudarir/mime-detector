# MIME Detector

A PHP library for detecting the real MIME type of files by analyzing their content.

Unlike extension-based detection, this library relies on multiple detection strategies:

* binary signatures (magic numbers);
* ZIP container analysis;
* text format inspection;
* ISO Base Media analysis (MP4, MOV, AVIF, HEIC...);
* RIFF container analysis (WEBP, WAV, AVI...);
* PHP FileInfo/libmagic detection.

The goal is to provide a reliable MIME type detection based on the actual file content.


## Requirements

* PHP >= 8.4
* `fileinfo` extension
* `zip` extension for ZIP container analysis


## Installation

Install the package using Composer:

```bash
composer require moudarir/mime-detector
```


## Detection Process

The detection pipeline follows a strict order:

```text
Magic Number Detection
      ↓
ZIP Container
      ↓
Text Detection
      ↓
Separated Values
      ↓
FileInfo/libmagic
      ↓
application/octet-stream fallback
```

Each detector returns:

* a `DetectionResult` when it recognizes the file;
* `null` when it cannot identify it.

The first successful detection is returned.

---

## Supported Formats

### Images

* JPEG
* PNG
* GIF
* BMP
* TIFF
* WEBP
* AVIF
* HEIC
* HEIF
* ICO
* SVG

---

### Documents

* PDF
* DOCX
* XLSX
* PPTX
* ODT
* ODS
* ODP
* EPUB

---

### Archives

* ZIP
* 7z
* RAR
* GZIP
* BZIP2
* XZ

---

### ZIP-based Containers

The library can identify specialized ZIP containers by inspecting their internal structure.

#### Microsoft Office Open XML

* DOCX
* XLSX
* PPTX

#### OpenDocument

* ODT
* ODS
* ODP

#### Other containers

* EPUB
* APK
* JAR

Detection is based on the internal ZIP structure, not the file extension.

---

### Audio

* MP3
* FLAC
* OGG
* WAV
* M4A

---

### Video

* MP4
* MOV
* AVI
* Matroska
* WebM
* 3GP

---

### Executables

* ELF
* Windows PE

---

### Disk Image

* ISO 9660
* Apple Disk Image `DMG`

---

### Text Formats

The library detects common text-based formats:

* PHP
* JSON
* XML
* HTML
* SVG
* CSS
* JavaScript
* Markdown
* YAML
* SQL

Generic text files are handled by PHP FileInfo when no specialized format is detected.

---

### Separated Values

* CSV
* TSV

---

## Example

Detecting a PDF file:

```php
<?php

use Moudarir\MimeDetector\Exceptions\MimeDetectorException;
use Moudarir\MimeDetector\Detector;

require_once '../vendor/autoload.php';

$root_path = dirname(__DIR__) . DIRECTORY_SEPARATOR;

$result = Detector::detect($root_path.'tests/Fixtures/document.pdf');

echo '<div style="margin-bottom: 20px;">';
echo '<h4 style="margin-bottom: 10px;">Mime Type</h4>';
echo '<pre>';
var_dump($result->mimeType(), $result->value());
echo '</pre>';
echo '</div>';

echo '<div style="margin-bottom: 20px;">';
echo '<h4 style="margin-bottom: 10px;">Detector</h4>';
echo '<pre>';
var_dump($result->detector(), $result->detectorValue());
echo '</pre>';
echo '</div>';

echo '<div style="margin-bottom: 20px;">';
echo '<h4 style="margin-bottom: 10px;">Metadata</h4>';
echo '<pre>';
var_dump(
    $result->filesize(),
    $result->dirname(),
    $result->basename(),
    $result->filename(),
    $result->extension(),
);
echo '</pre>';
echo '</div>';
```

Output:

### Mime Type

```text
enum(Moudarir\MimeDetector\Enum\MimeType::PDF)
string(15) "application/pdf"
```

### Detector

```text
enum(Moudarir\MimeDetector\Enum\DetectorSource::MAGIC_NUMBER)
string(12) "magic_number"
```

### Metadata

```text
int(70011)
string(47) "/Users/user/htdocs/mime-detector/tests/Fixtures"
string(12) "document.pdf"
string(8) "document"
string(3) "pdf"
```

---

## Design Principles

### Content over extension

The file extension is never used as the primary source of truth.

Example:

A file named:

```text
image.jpg
```

containing a PDF document will be detected as:

```text
application/pdf
```

---

## Performance Considerations

The library includes several optimizations:

* preload of the file header;
* caching of binary header data;
* caching of hexadecimal header representation;
* stopping detection as soon as a detector succeeds;
* avoiding ZIP parsing unless the file is identified as a ZIP archive.

---

## Limitations

MIME detection is not a complete security validation mechanism.

A file can have a valid MIME type while still containing malicious content.

This library should not replace:

* antivirus scanning;
* application-level validation;
* security policies.

It only identifies the actual file format.

---

## License

MIT License
