<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Enum;

enum MimeType: string
{

    /*
     * Images
     */
    case JPEG = 'image/jpeg';
    case PNG = 'image/png';
    case GIF = 'image/gif';
    case BMP = 'image/bmp';
    case TIFF = 'image/tiff';
    case WEBP = 'image/webp';
    case AVIF = 'image/avif';
    case HEIC = 'image/heic';
    case HEIF = 'image/heif';

    case ICO = 'image/x-icon';
    case SVG = 'image/svg+xml';

    /*
     * Documents
     */
    case PDF = 'application/pdf';

    /*
     * Archives
     */
    case ZIP = 'application/zip';
    case SEVEN_ZIP = 'application/x-7z-compressed';
    case RAR = 'application/vnd.rar';
    case GZIP = 'application/gzip';
    case BZIP2 = 'application/x-bzip2';
    case XZ = 'application/x-xz';

    /*
     * Microsoft Office Open XML
     */
    case DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    case XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    case PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

    /*
     * OpenDocument
     */
    case ODT = 'application/vnd.oasis.opendocument.text';
    case ODS = 'application/vnd.oasis.opendocument.spreadsheet';
    case ODP = 'application/vnd.oasis.opendocument.presentation';

    /*
     * Containers
     */
    case EPUB = 'application/epub+zip';
    case JAR = 'application/java-archive';
    case APK = 'application/vnd.android.package-archive';

    /*
     * Audio
     */
    case MP3 = 'audio/mpeg';
    case FLAC = 'audio/flac';
    case OGG = 'application/ogg';
    case WAV = 'audio/wav';
    case M4A = 'audio/mp4';

    /*
     * Video
     */
    case MP4 = 'video/mp4';
    case AVI = 'video/x-msvideo';
    case MATROSKA = 'video/x-matroska';
    case THREE_GPP = 'video/3gpp';
    case WEBM = 'video/webm';
    case MOV = 'video/quicktime';

    /*
     * Executables
     */
    case ELF = 'application/x-elf';
    case WINDOWS_EXECUTABLE = 'application/vnd.microsoft.portable-executable';

    /*
     * Text / fallback FileInfo
     */
    case TEXT_PLAIN = 'text/plain';
    case TEXT_CSV = 'text/csv';
    case XML = 'application/xml';
    case JSON = 'application/json';
    case HTML = 'text/html';
    case PHP = 'application/x-httpd-php';
    case CSS = 'text/css';
    case JAVASCRIPT = 'application/javascript';
    case MARKDOWN = 'text/markdown';
    case YAML = 'application/yaml';
    case SQL = 'application/sql';
    case TSV = 'text/tab-separated-values';

    /*
     * Disk images
     */
    case ISO = 'application/x-iso9660-image';
    case DMG = 'application/x-apple-diskimage';

    case OCTET_STREAM = 'application/octet-stream';

    public function isImage(): bool
    {
        return match ($this) {
            self::JPEG,
            self::PNG,
            self::GIF,
            self::BMP,
            self::TIFF,
            self::WEBP,
            self::AVIF,
            self::HEIC,
            self::HEIF,
            self::ICO,
            self::SVG => true,
            default => false,
        };
    }

    public function isArchive(): bool
    {
        return match ($this) {
            self::ZIP,
            self::SEVEN_ZIP,
            self::RAR,
            self::GZIP,
            self::BZIP2,
            self::XZ => true,
            default => false,
        };
    }

    public function isDocument(): bool
    {
        return match ($this) {
            self::PDF,
            self::DOCX,
            self::XLSX,
            self::PPTX,
            self::ODT,
            self::ODS,
            self::ODP,
            self::EPUB => true,
            default => false,
        };
    }
}
