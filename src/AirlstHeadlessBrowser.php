<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;

final readonly class AirlstHeadlessBrowser implements HeadlessBrowser
{
    private const API_URL = 'https://headless-browser.airlst.app/api';

    public function __construct(
        private string $apiKey,
        private ClientInterface $client = new Client()
    ) {}

    public function pdf(
        string $html,
        string $format = 'A4',
        array $margins = [10, 10, 10, 10]
    ): Response {
        $request = new Request(
            'POST',
            self::API_URL . '/pdf',
            $this->requestHeaders(),
            json_encode([
                'html' => $html,
                'format' => $format,
                'margins' => $margins,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->sendRequest($request);

        return new Response($response);
    }

    public function jpeg(
        string $html,
        int $quality = 75
    ): Response {
        $request = new Request(
            'POST',
            self::API_URL . '/jpeg',
            $this->requestHeaders(),
            json_encode([
                'html' => $html,
                'quality' => $quality,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->sendRequest($request);

        return new Response($response);
    }

    private function requestHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->apiKey}",
        ];
    }
}
