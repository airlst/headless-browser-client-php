<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

interface HeadlessBrowser
{
    public function withoutHtmlSanitization(): self;

    public function pdf(string $html, array $margins = [10, 10, 10, 10], ?string $format = null, ?string $width = null, ?string $height = null): Response;

    public function jpeg(string $html, int $quality = 75): Response;
}
