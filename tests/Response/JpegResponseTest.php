<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClientPhp\Tests\Response;

use Airlst\HeadlessBrowserClientPhp\Response\JpegResponse;
use Airlst\HeadlessBrowserClientPhp\Response\Response;
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
