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

    public function testTemporaryUrlMethodReturnsBodyItemFromResponse(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(json_encode(['temporary_url' => 'foo']));

        $this->assertSame('foo', (new Response($response))->temporaryUrl());
    }

    public function testContentsMethodThrowsRuntimeExceptionFetchingTemporaryUrlFails(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(json_encode(['temporary_url' => 'foo']));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch the file contents.');

        (new Response($response))->contents();
    }

    public function testContentsMethodFetchesTemporaryUrlContents(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(json_encode(['temporary_url' => 'https://fakeimg.pl/4x4/']));

        $this->assertSame(
            file_get_contents('https://fakeimg.pl/4x4/'),
            (new Response($response))->contents()
        );
    }
}
