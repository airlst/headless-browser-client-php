<?php

declare(strict_types=1);

namespace Airlst\HeadlessBrowserClient;

use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use stdClass;

use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function strlen;

final readonly class InspectResponse
{
    /**
     * @var list<array{viewport: array{width: int, height: int}, scenario: string, ok: bool, error: ?string, result: ?array<string, mixed>, tiles: list<array{url: string, x: int, y: int, width: int, height: int}>, total_tiles: int}>
     */
    private array $renders;

    private bool $timedOut;

    public function __construct(ResponseInterface $response, private ClientInterface $client)
    {
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(
                "Headless browser request failed with status code returned {$response->getStatusCode()}."
            );
        }

        try {
            $body = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw self::invalidResponse();
        }

        if (! $body instanceof stdClass || ! isset($body->renders, $body->timed_out) || ! is_array($body->renders) || ! array_is_list($body->renders) || ! is_bool($body->timed_out)) {
            throw self::invalidResponse();
        }

        $renders = [];
        foreach ($body->renders as $render) {
            if (! $render instanceof stdClass
                || ! isset($render->viewport, $render->scenario, $render->ok, $render->tiles, $render->total_tiles)
                || ! property_exists($render, 'error')
                || ! property_exists($render, 'result')
                || ! $render->viewport instanceof stdClass
                || ! isset($render->viewport->width, $render->viewport->height)
                || ! is_int($render->viewport->width)
                || ! is_int($render->viewport->height)
                || ! is_string($render->scenario)
                || ! is_bool($render->ok)
                || ($render->error !== null && ! is_string($render->error))
                || ($render->result !== null && ! $render->result instanceof stdClass)
                || ! is_array($render->tiles)
                || ! array_is_list($render->tiles)
                || ! is_int($render->total_tiles)
            ) {
                throw self::invalidResponse();
            }

            $tiles = [];
            foreach ($render->tiles as $tile) {
                if (! $tile instanceof stdClass
                    || ! isset($tile->url, $tile->x, $tile->y, $tile->width, $tile->height)
                    || ! is_string($tile->url)
                    || ! is_int($tile->x)
                    || ! is_int($tile->y)
                    || ! is_int($tile->width)
                    || ! is_int($tile->height)
                ) {
                    throw self::invalidResponse();
                }

                $tiles[] = [
                    'url' => $tile->url,
                    'x' => $tile->x,
                    'y' => $tile->y,
                    'width' => $tile->width,
                    'height' => $tile->height,
                ];
            }

            $renders[] = [
                'viewport' => ['width' => $render->viewport->width, 'height' => $render->viewport->height],
                'scenario' => $render->scenario,
                'ok' => $render->ok,
                'error' => $render->error,
                'result' => $render->result === null ? null : self::normalizeObject($render->result),
                'tiles' => $tiles,
                'total_tiles' => $render->total_tiles,
            ];
        }

        $this->renders = $renders;
        $this->timedOut = $body->timed_out;
    }

    /**
     * @return list<array{viewport: array{width: int, height: int}, scenario: string, ok: bool, error: ?string, result: ?array<string, mixed>, tiles: list<array{url: string, x: int, y: int, width: int, height: int}>, total_tiles: int}>
     */
    public function renders(): array
    {
        return $this->renders;
    }

    public function timedOut(): bool
    {
        return $this->timedOut;
    }

    public function tileContents(string $url, int $maxBytes): string
    {
        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['scheme'], $parts['host']) || strtolower($parts['scheme']) !== 'https') {
            throw new RuntimeException('Headless browser tile URL must use HTTPS.');
        }

        if ($maxBytes < 0) {
            throw new RuntimeException('Headless browser tile exceeds maximum size.');
        }

        $response = $this->client->sendRequest(new Request('GET', $url));
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException("Headless browser tile request failed with status code {$response->getStatusCode()}.");
        }

        $length = $response->getHeaderLine('Content-Length');
        if ($length !== '' && (! ctype_digit($length) || filter_var($length, FILTER_VALIDATE_INT) === false || (int) $length > $maxBytes)) {
            throw new RuntimeException('Headless browser tile exceeds maximum size.');
        }

        $contents = '';
        $stream = $response->getBody();
        $consecutiveEmptyReads = 0;
        while (! $stream->eof()) {
            $chunk = $stream->read(min(8192, $maxBytes - strlen($contents) + 1));
            if ($chunk === '') {
                ++$consecutiveEmptyReads;
                if ($consecutiveEmptyReads > 100) {
                    throw new RuntimeException('Headless browser tile download was incomplete.');
                }

                continue;
            }

            $consecutiveEmptyReads = 0;
            $contents .= $chunk;
            if (strlen($contents) > $maxBytes) {
                throw new RuntimeException('Headless browser tile exceeds maximum size.');
            }
        }

        if ($length !== '' && strlen($contents) !== (int) $length) {
            throw new RuntimeException('Headless browser tile download was incomplete.');
        }

        if (! str_starts_with($contents, "\x89PNG\r\n\x1a\n")) {
            throw new RuntimeException('Headless browser tile is not a PNG.');
        }

        return $contents;
    }

    private static function invalidResponse(): RuntimeException
    {
        return new RuntimeException('Headless browser returned an invalid inspect response.');
    }

    /** @return array<string, mixed> */
    private static function normalizeObject(stdClass $object): array
    {
        $result = [];
        foreach (get_object_vars($object) as $key => $value) {
            $result[$key] = self::normalizeValue($value);
        }

        return $result;
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof stdClass) {
            return self::normalizeObject($value);
        }

        if (is_array($value)) {
            return array_map(self::normalizeValue(...), $value);
        }

        return $value;
    }
}
