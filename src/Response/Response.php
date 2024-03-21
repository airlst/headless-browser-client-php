<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Response;

use Psr\Http\Message\ResponseInterface;

abstract readonly class Response
{
    protected array $body;

    public function __construct(ResponseInterface $response)
    {
        /** @var array $body */
        $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $this->body = $body;
    }
}
