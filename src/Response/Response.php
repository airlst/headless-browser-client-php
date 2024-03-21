<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClientPhp\Response;

use Psr\Http\Message\ResponseInterface;

abstract readonly class Response
{
    protected array $body;

    public function __construct(ResponseInterface $response)
    {
        /** @var array $body */
        $body = json_decode($response->getBody()->getContents(), true);
        $this->body = $body;
    }
}
