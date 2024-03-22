<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

interface HeadlessBrowser
{
    public function pdf(string $html, string $format = 'A4', array $margins = [10, 10, 10, 10]): Response;

    public function jpeg(string $html, int $quality = 75): Response;
}
