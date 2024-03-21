<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Response;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

abstract readonly class Response
{
    protected array $body;

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
}
