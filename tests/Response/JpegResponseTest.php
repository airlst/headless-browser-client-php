<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Tests\Response;

use Airlst\HeadlessBrowserClient\Response\JpegResponse;
use Airlst\HeadlessBrowserClient\Response\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
final class JpegResponseTest extends TestCase
{
    public function testJpegMethodReturnsBodyItemFromResponse(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody->getContents')->andReturn('{"jpeg": "image"}');

        $jpeg = new JpegResponse($response);

        $this->assertSame('image', $jpeg->contents());
    }

    public function testSubclassFromResponse(): void
    {
        $this->assertTrue(
            is_subclass_of(JpegResponse::class, Response::class)
        );
    }
}
