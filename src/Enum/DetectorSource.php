<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Enum;

enum DetectorSource: string
{

    case DISK_IMAGE = 'disk_image';
    case FILE_INFO = 'file_info';
    case IMAGE = 'image';
    case ISO_BASE_MEDIA = 'iso_base_media';
    case MAGIC_NUMBER = 'magic_number';
    case RIFF = 'riff';
    case TEXT = 'text';
    case ZIP = 'zip';
    case FALLBACK = 'fallback';
}
