<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Override;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final readonly class AirlstHeadlessBrowser implements HeadlessBrowser
{
    private const string API_URL = 'https://headless-browser.airlst.app/api';

    public function __construct(
        private string $apiKey,
        private ClientInterface $client = new Client()
    ) {}

    #[Override]
    public function pdf(
        string $html,
        string $format = 'A4',
        array $margins = [10, 10, 10, 10]
    ): Response {
        $request = $this->prepareRequest('/pdf', [
            'html' => $html,
            'format' => $format,
            'margins' => $margins,
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
