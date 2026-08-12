# MIME Detector

A PHP library for detecting the real MIME type of files by analyzing their content.

Unlike extension-based detection, this library relies on multiple detection strategies:

* binary signatures (magic numbers);
* ZIP container analysis;
* text format inspection;
* ISO Base Media analysis (MP4, MOV, AVIF, HEIC...);
* RIFF container analysis (WEBP, WAV, AVI...);
* PHP FileInfo/libmagic fallback.

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


## Basic Usage

```php
use Moudarir\MimeDetector\Exceptions\MimeTypeException;
use Moudarir\MimeDetector\MimeType;

try {
    $result = MimeType::detect('/path/to/file.pdf');
    echo $result->mimeValue();
} catch (MimeTypeException $exception) {
    echo $exception->getMessage();
}
```

Output:

```text
application/pdf
```

You can also retrieve which detector identified the file:

```php
echo $result->detector();
```

Example output:

```text
Moudarir\MimeDetector\Detector\MagicNumberMimeDetector
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
* CSV
* TSV

Generic text files are handled by PHP FileInfo when no specialized format is detected.

---

## Example

Detecting a DOCX file:

```php
use Moudarir\MimeDetector\Exceptions\MimeTypeException;
use Moudarir\MimeDetector\MimeType;

try {
    $result = MimeType::detect('/path/to/file.pdf');
    echo $result->mimeValue();
    echo PHP_EOL;
    echo $result->detector();
} catch (MimeTypeException $exception) {
    echo $exception->getMessage();
}
```

Output:

```text
application/vnd.openxmlformats-officedocument.wordprocessingml.document

Moudarir\MimeDetector\Detector\ZipMimeDetector
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

* lazy loading of file headers;
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
