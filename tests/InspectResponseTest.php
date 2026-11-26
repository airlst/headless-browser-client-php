<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Tests;

use Airlst\HeadlessBrowserClient\InspectResponse;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function strlen;

/**
 * @internal
 */
final class InspectResponseTest extends TestCase
{
    public function testNormalizesRendersAndTimedOut(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $body = '{"renders":[{"viewport":{"width":390,"height":844},"scenario":"default","ok":true,"error":null,"result":{"findings":[],"capture":{"mode":"tiles"}},"tiles":[{"url":"https://bucket.example/tile.png","x":0,"y":0,"width":390,"height":844}],"total_tiles":3},{"viewport":{"width":1440,"height":900},"scenario":"state:1","ok":false,"error":"navigation failed","result":null,"tiles":[],"total_tiles":0}],"timed_out":true}';

        $inspect = new InspectResponse(new Response(200, [], $body), $client);

        $this->assertSame([
            [
                'viewport' => ['width' => 390, 'height' => 844],
                'scenario' => 'default',
                'ok' => true,
                'error' => null,
                'result' => ['findings' => [], 'capture' => ['mode' => 'tiles']],
                'tiles' => [['url' => 'https://bucket.example/tile.png', 'x' => 0, 'y' => 0, 'width' => 390, 'height' => 844]],
                'total_tiles' => 3,
            ],
            [
                'viewport' => ['width' => 1440, 'height' => 900],
                'scenario' => 'state:1',
                'ok' => false,
                'error' => 'navigation failed',
                'result' => null,
                'tiles' => [],
                'total_tiles' => 0,
            ],
        ], $inspect->renders());
        $this->assertTrue($inspect->timedOut());
    }

    #[DataProvider('invalidBodies')]
    public function testRejectsMalformedInspectBody(string $body): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Headless browser returned an invalid inspect response.');

        new InspectResponse(new Response(200, [], $body), Mockery::mock(ClientInterface::class));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidBodies(): iterable
    {
        yield 'invalid JSON' => ['{'];

        yield 'missing renders' => ['{"timed_out":false}'];

        yield 'wrong timed out type' => ['{"renders":[],"timed_out":0}'];

        yield 'renders not list' => ['{"renders":{},"timed_out":false}'];

        yield 'missing render field' => ['{"renders":[{"viewport":{"width":390,"height":844}}],"timed_out":false}'];

        yield 'wrong viewport field type' => ['{"renders":[{"viewport":{"width":"390","height":844},"scenario":"default","ok":true,"error":null,"result":{},"tiles":[],"total_tiles":0}],"timed_out":false}'];

        yield 'result not object' => ['{"renders":[{"viewport":{"width":390,"height":844},"scenario":"default","ok":true,"error":null,"result":[],"tiles":[],"total_tiles":0}],"timed_out":false}'];

        yield 'tile missing coordinate' => ['{"renders":[{"viewport":{"width":390,"height":844},"scenario":"default","ok":true,"error":null,"result":{},"tiles":[{"url":"https://bucket.example/tile.png","x":0,"width":390,"height":844}],"total_tiles":1}],"timed_out":false}'];

        yield 'tile coordinate wrong type' => ['{"renders":[{"viewport":{"width":390,"height":844},"scenario":"default","ok":true,"error":null,"result":{},"tiles":[{"url":"https://bucket.example/tile.png","x":0,"y":0.5,"width":390,"height":844}],"total_tiles":1}],"timed_out":false}'];
    }

    public function testRejectsNon200ResponseWithStatus(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Headless browser request failed with status code returned 503.');

        new InspectResponse(new Response(503), Mockery::mock(ClientInterface::class));
    }

    public function testDownloadsPngTileWithoutAuthorization(): void
    {
        $png = "\x89PNG\r\n\x1a\nimage-data";
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request): bool {
                return $request->getMethod() === 'GET'
                    && (string) $request->getUri() === 'https://bucket.example/tile.png?signature=abc'
                    && ! $request->hasHeader('Authorization');
            })
            ->andReturn(new Response(200, ['Content-Length' => (string) strlen($png)], $png));

        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->assertSame($png, $inspect->tileContents('https://bucket.example/tile.png?signature=abc', 100));
    }

    public function testContinuesAfterAnEmptyReadBeforeEof(): void
    {
        $png = "\x89PNG\r\n\x1a\nimage-data";
        $stream = Mockery::mock(StreamInterface::class);
        $stream->shouldReceive('eof')->andReturn(false, false, false, true);
        $stream->shouldReceive('read')->andReturn("\x89PNG\r\n\x1a\n", '', 'image-data');
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')->once()->andReturn(new Response(200, [], $stream));

        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->assertSame($png, $inspect->tileContents('https://bucket.example/tile.png', 100));
    }

    public function testRejectsStreamThatOnlyReturnsEmptyReadsWithoutEof(): void
    {
        $stream = Mockery::mock(StreamInterface::class);
        $stream->shouldReceive('eof')->andReturn(false);
        $stream->shouldReceive('read')->andReturn('');
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')->once()->andReturn(new Response(200, [], $stream));

        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Headless browser tile download was incomplete.');
        $inspect->tileContents('https://bucket.example/tile.png', 100);
    }

    public function testRejectsBodyShorterThanContentLength(): void
    {
        $png = "\x89PNG\r\n\x1a\nimage-data";
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')->once()->andReturn(new Response(200, ['Content-Length' => (string) (strlen($png) + 4)], $png));

        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Headless browser tile download was incomplete.');
        $inspect->tileContents('https://bucket.example/tile.png', 100);
    }

    public function testRejectsTileOverSizeByHeader(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')->once()->andReturn(new Response(200, ['Content-Length' => '101'], "\x89PNG\r\n\x1a\n"));

        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->expectException(RuntimeException::class);
        $inspect->tileContents('https://bucket.example/tile.png', 100);
    }

    public function testRejectsTileOverSizeByBody(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')->once()->andReturn(new Response(200, [], "\x89PNG\r\n\x1a\n" . str_repeat('x', 101)));

        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->expectException(RuntimeException::class);
        $inspect->tileContents('https://bucket.example/tile.png', 100);
    }

    public function testRejectsNonPngTile(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')->once()->andReturn(new Response(200, [], 'not-a-png'));

        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->expectException(RuntimeException::class);
        $inspect->tileContents('https://bucket.example/tile.png', 100);
    }

    public function testRejectsNonHttpsTileBeforeRequest(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldNotReceive('sendRequest');
        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->expectException(RuntimeException::class);
        $inspect->tileContents('http://bucket.example/tile.png', 100);
    }

    public function testRejectsNon200TileResponseWithStatus(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')->once()->andReturn(new Response(403));
        $inspect = new InspectResponse(new Response(200, [], '{"renders":[],"timed_out":false}'), $client);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('403');
        $inspect->tileContents('https://bucket.example/tile.png', 100);
    }
}
