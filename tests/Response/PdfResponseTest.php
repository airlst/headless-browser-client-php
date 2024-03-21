<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClientPhp\Tests\Response;

use Airlst\HeadlessBrowserClientPhp\Response\PdfResponse;
use Airlst\HeadlessBrowserClientPhp\Response\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
final class PdfResponseTest extends TestCase
{
    public function testPdfMethodReturnsBodyItemFromResponse(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody->getContents')->andReturn('{"pdf": "pdf content"}');

        $pdf = new PdfResponse($response);

        $this->assertSame('pdf content', $pdf->contents());
    }

    public function testSubclassFromResponse(): void
    {
        $this->assertTrue(
            is_subclass_of(PdfResponse::class, Response::class)
        );
    }
}