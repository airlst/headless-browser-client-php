<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Response;

readonly class JpegResponse extends Response
{
    public function contents(): string
    {
        return $this->body['jpeg'];
    }
}
