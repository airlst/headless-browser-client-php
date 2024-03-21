<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClientPhp\Tests;

use Airlst\HeadlessBrowserClientPhp\HeadlessBrowser;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class HeadlessBrowserTest extends TestCase
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

                return $request->getBody()->getContents() === '{"html":"<p>html<\/p>","format":"A3","margins":[5,5,5,5]}';
            })
            ->andReturn(new Response(200, [], '{"pdf":"pdf content"}'));

        $pdf = (new HeadlessBrowser($client, 'api-key'))->pdf('<p>html</p>', 'A3', [5, 5, 5, 5]);

        $this->assertSame('pdf content', $pdf->contents());
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
            ->andReturn(new Response(200, [], '{"jpeg":"jpeg content"}'));

        $jpeg = (new HeadlessBrowser($client, 'api-key'))->jpeg('<p>html</p>', 95);

        $this->assertSame('jpeg content', $jpeg->contents());
    }
}