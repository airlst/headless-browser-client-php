<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Tests\Response;

use Airlst\HeadlessBrowserClient\Response\Response;
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

        new TestResponse($response);
    }
}

final readonly class TestResponse extends Response {}
