<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Override;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class AirlstHeadlessBrowser implements HeadlessBrowser
{
    private const string API_URL = 'https://headless-browser.airlst.app/api';

    private bool $sanitizeHtml = true;

    public function __construct(
        private string $apiKey,
        private ClientInterface $client = new Client()
    ) {}

    public function withoutHtmlSanitization(): self
    {
        $this->sanitizeHtml = false;

        return $this;
    }

    #[Override]
    public function pdf(
        string $html,
        array $margins = [10, 10, 10, 10],
        ?string $format = null,
        ?string $width = null,
        ?string $height = null,
    ): Response {
        $request = $this->prepareRequest('/pdf', [
            'html' => $html,
            'format' => $format,
            'margins' => $margins,
            'width' => $width,
            'height' => $height,
            'sanitize' => $this->sanitizeHtml,
        ]);

        $response = $this->client->sendRequest($request);

        return new Response($response);
    }

    #[Override]
    public function jpeg(
        string $html,
        int $quality = 75
    ): Response {
        $request = $this->prepareRequest('/jpeg', [
            'html' => $html,
            'quality' => $quality,
        ]);

        $response = $this->client->sendRequest($request);

        return new Response($response);
    }

    /**
     * @param list<array{width: int, height: int}>                $viewports
     * @param list<array{key: string, arg: array<string, mixed>}> $scenarios
     */
    #[Override]
    public function inspect(
        string $url,
        array $viewports,
        array $scenarios,
        int $maxTiles = 6,
        int $deadlineMs = 20000,
    ): InspectResponse {
        $request = $this->prepareRequest('/inspect', [
            'url' => $url,
            'viewports' => $viewports,
            'scenarios' => array_map(
                static fn (array $scenario): array => [
                    'key' => $scenario['key'],
                    'arg' => (object) $scenario['arg'],
                ],
                $scenarios,
            ),
            'max_tiles' => $maxTiles,
            'deadline_ms' => $deadlineMs,
        ]);

        return new InspectResponse($this->client->sendRequest($request), $this->client);
    }

    private function prepareRequest(string $uri, array $payload): RequestInterface // @phpstan-ignore-line
    {
        return new Request(
            'POST',
            self::API_URL . $uri,
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$this->apiKey}",
            ],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }
}
