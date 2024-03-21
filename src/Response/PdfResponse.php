<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Response;

use RuntimeException;

final readonly class PdfResponse extends Response
{
    public function contents(): string
    {
        $contents = base64_decode((string) $this->body['pdf'], true);

        if ($contents === false) {
            throw new RuntimeException('Failed to decode PDF contents.');
        }

        return $contents;
    }
}
