<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Response;

class PdfResponse extends Response
{
    public function contents(): string
    {
        return $this->body['pdf'];
    }
}
