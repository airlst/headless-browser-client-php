<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Tests;

use Airlst\HeadlessBrowserClient\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * @internal
 */
final class ResponseTest extends TestCase
{
    public function testThrowsRuntimeExceptionOnNonSuccessfulResponses(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->once()->withNoArgs()->andReturn(404);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Headless browser request failed with status code returned 404.');

        new Response($response);
    }

    public function testJpegMethodReturnsBodyItemFromResponse(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(json_encode(['contents' => base64_encode('image')]));

        $this->assertSame('image', (new Response($response))->contents());
    }

    public function testThrowsRuntimeExceptionOnInvalidBase64EncodedContent(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(json_encode(['contents' => 'not-encoded']));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode contents.');

        (new Response($response))->contents();
    }
}
