<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient\Tests;

use Airlst\HeadlessBrowserClient\AirlstHeadlessBrowser;
use Airlst\HeadlessBrowserClient\HeadlessBrowser;
use Airlst\HeadlessBrowserClient\InspectResponse;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class AirlstHeadlessBrowserTest extends TestCase
{
    public function testRequestsPdfContents(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request): bool {
                if ($request->getMethod() !== 'POST') {
                    return false;
                }

                if ($request->getUri()->__toString() !== 'https://headless-browser.airlst.app/api/pdf') {
                    return false;
                }

                if ($request->getHeaders()['Content-Type'][0] !== 'application/json') {
                    return false;
                }

                if ($request->getHeaders()['Accept'][0] !== 'application/json') {
                    return false;
                }

                if ($request->getHeaders()['Authorization'][0] !== 'Bearer api-key') {
                    return false;
                }

                return $request->getBody()->getContents() === '{"html":"<p>html<\/p>","format":"A4","margins":[5,5,5,5],"width":null,"height":null,"sanitize":true}';
            })
            ->andReturn(new Response(200, [], json_encode(['temporary_url' => 'http://example.com/pdf'])));

        $pdf = (new AirlstHeadlessBrowser('api-key', $client))->pdf('<p>html</p>', [5, 5, 5, 5], 'A4');

        $this->assertSame('http://example.com/pdf', $pdf->temporaryUrl());
    }

    public function testRequestsJpegContents(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request): bool {
                if ($request->getMethod() !== 'POST') {
                    return false;
                }

                if ($request->getUri()->__toString() !== 'https://headless-browser.airlst.app/api/jpeg') {
                    return false;
                }

                if ($request->getHeaders()['Content-Type'][0] !== 'application/json') {
                    return false;
                }

                if ($request->getHeaders()['Accept'][0] !== 'application/json') {
                    return false;
                }

                if ($request->getHeaders()['Authorization'][0] !== 'Bearer api-key') {
                    return false;
                }

                return $request->getBody()->getContents() === '{"html":"<p>html<\/p>","quality":95}';
            })
            ->andReturn(new Response(200, [], json_encode(['temporary_url' => 'http://example.com/jpeg'])));

        $jpeg = (new AirlstHeadlessBrowser('api-key', $client))->jpeg('<p>html</p>', 95);

        $this->assertSame('http://example.com/jpeg', $jpeg->temporaryUrl());
    }

    public function testAcceptsCustomSize(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request): bool {
                $inputs = json_decode($request->getBody()->getContents(), true);

                return $inputs['width'] === 'customWidth' && $inputs['height'] === 'customHeight';
            })
            ->andReturn(new Response(200, [], json_encode(['temporary_url' => 'http://example.com/pdf'])));

        $pdf = (new AirlstHeadlessBrowser('api-key', $client))->pdf('<p>html</p>', width: 'customWidth', height: 'customHeight', margins: [5, 5, 5, 5]);

        $this->assertSame('http://example.com/pdf', $pdf->temporaryUrl());
    }

    public function testByDefaultSanitizesHtmlIsTrue(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request): bool {
                $inputs = json_decode($request->getBody()->getContents(), true);

                return $inputs['sanitize'] === true;
            })
            ->andReturn(new Response(200, [], json_encode(['temporary_url' => 'http://example.com/pdf'])));

        $pdf = (new AirlstHeadlessBrowser('api-key', $client))->pdf('<p>html</p>', width: 'customWidth', height: 'customHeight', margins: [5, 5, 5, 5]);

        $this->assertSame('http://example.com/pdf', $pdf->temporaryUrl());
    }

    public function testAllowToSkipSanitation(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request): bool {
                $inputs = json_decode($request->getBody()->getContents(), true);

                return $inputs['sanitize'] === false;
            })
            ->andReturn(new Response(200, [], json_encode(['temporary_url' => 'http://example.com/pdf'])));

        $pdf = (new AirlstHeadlessBrowser('api-key', $client))->withoutHtmlSanitization()->pdf('<p>html</p>', width: 'customWidth', height: 'customHeight', margins: [5, 5, 5, 5]);

        $this->assertSame('http://example.com/pdf', $pdf->temporaryUrl());
    }

    public function testImplementsHeadlessBrowser(): void
    {
        $this->assertTrue(
            is_subclass_of(AirlstHeadlessBrowser::class, HeadlessBrowser::class)
        );
    }

    public function testInspectPostsContractBodyAndHeaders(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (RequestInterface $request): bool {
                return $request->getMethod() === 'POST'
                    && (string) $request->getUri() === 'https://headless-browser.airlst.app/api/inspect'
                    && $request->getHeaderLine('Content-Type') === 'application/json'
                    && $request->getHeaderLine('Accept') === 'application/json'
                    && $request->getHeaderLine('Authorization') === 'Bearer api-key'
                    && (string) $request->getBody() === '{"url":"https:\/\/airlst.app\/preview","viewports":[{"width":390,"height":844}],"scenarios":[{"key":"default","arg":{"state":null}},{"key":"empty","arg":{}}],"max_tiles":4,"deadline_ms":17000}';
            })
            ->andReturn(new Response(200, [], '{"renders":[],"timed_out":false}'));

        $inspect = (new AirlstHeadlessBrowser('api-key', $client))->inspect(
            'https://airlst.app/preview',
            [['width' => 390, 'height' => 844]],
            [['key' => 'default', 'arg' => ['state' => null]], ['key' => 'empty', 'arg' => []]],
            4,
            17000,
        );

        $this->assertInstanceOf(InspectResponse::class, $inspect);
        $this->assertSame([], $inspect->renders());
    }

    public function testInspectSerializesEmptyScenariosAndDefaultLimits(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('sendRequest')
            ->once()
            ->withArgs(fn (RequestInterface $request): bool => (string) $request->getBody() === '{"url":"https:\/\/airlst.app\/preview","viewports":[{"width":1440,"height":900}],"scenarios":[],"max_tiles":6,"deadline_ms":20000}')
            ->andReturn(new Response(200, [], '{"renders":[],"timed_out":false}'));

        $inspect = (new AirlstHeadlessBrowser('api-key', $client))->inspect(
            'https://airlst.app/preview',
            [['width' => 1440, 'height' => 900]],
            [],
        );

        $this->assertSame([], $inspect->renders());
    }
}
