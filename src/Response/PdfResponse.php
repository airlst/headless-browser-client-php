<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClientPhp\Response;

final readonly class PdfResponse extends Response
{
    public function contents(): string
    {
        return $this->body['pdf'];
    }
}
