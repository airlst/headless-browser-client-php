<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

use Airlst\HeadlessBrowserClient\Response\JpegResponse;
use Airlst\HeadlessBrowserClient\Response\PdfResponse;

interface HeadlessBrowser
{
    public function pdf(string $html, string $format = 'A4', array $margins = [10, 10, 10, 10]): PdfResponse;

    public function jpeg(string $html, int $quality = 75): JpegResponse;
}
