<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

interface HeadlessBrowser
{
    public function withoutHtmlSanitization(): self;

    public function pdf(string $html, array $margins = [10, 10, 10, 10], ?string $format = null, ?string $width = null, ?string $height = null): Response;

    public function jpeg(string $html, int $quality = 75): Response;

    /**
     * @param list<array{width: int, height: int}>                $viewports
     * @param list<array{key: string, arg: array<string, mixed>}> $scenarios
     */
    public function inspect(
        string $url,
        array $viewports,
        array $scenarios,
        int $maxTiles = 6,
        int $deadlineMs = 20000,
    ): InspectResponse;
}
