<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Tests\Response;

use Airlst\HeadlessBrowserClient\Response\JpegResponse;
use Airlst\HeadlessBrowserClient\Response\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * @internal
 */
final class JpegResponseTest extends TestCase
{
    public function testJpegMethodReturnsBodyItemFromResponse(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(json_encode(['jpeg' => base64_encode('image')]));

        $jpeg = new JpegResponse($response);

        $this->assertSame('image', $jpeg->contents());
    }

    public function testThrowsRuntimeExceptionOnInvalidBase64EncodedContent(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(json_encode(['jpeg' => 'not-encoded']));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode JPEG contents.');

        (new JpegResponse($response))->contents();
    }

    public function testSubclassFromResponse(): void
    {
        $this->assertTrue(
            is_subclass_of(JpegResponse::class, Response::class)
        );
    }
}
