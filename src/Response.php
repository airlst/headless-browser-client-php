<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final readonly class Response
{
    private array $body;

    public function __construct(ResponseInterface $response)
    {
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(
                "Headless browser request failed with status code returned {$response->getStatusCode()}."
            );
        }

        /** @var array $body */
        $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $this->body = $body;
    }

    public function temporaryUrl(): string
    {
        return $this->body['temporary_url'];
    }

    public function contents(): string
    {
        $contents = @file_get_contents($this->temporaryUrl(), true);

        if ($contents === false) {
            throw new RuntimeException('Failed to fetch the file contents.');
        }

        return $contents;
    }
}
