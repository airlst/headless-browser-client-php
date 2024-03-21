<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Response;

use RuntimeException;

final readonly class JpegResponse extends Response
{
    public function contents(): string
    {
        $contents = base64_decode((string) $this->body['jpeg'], true);

        if ($contents === false) {
            throw new RuntimeException('Failed to decode JPEG contents.');
        }

        return $contents;
    }
}
