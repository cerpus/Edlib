<?php

declare(strict_types=1);

namespace App\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

use function http_build_query;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class NynorobotAdapter implements TranslationServiceInterface
{
    public const STYLE_MODERATE = 'Moderat nynorsk';
    public const STYLE_RADICAL = 'Radikal nynorsk';
    public const STYLE_CONSERVATIVE = 'Konservativ nynorsk';

    /**
     * @param self::STYLE_* $style
     * @param int<1, max> $concurrentRequests
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $style,
        private readonly int $concurrentRequests = 5,
    ) {
    }

    public function getTranslations(H5PTranslationDataObject $data): H5PTranslationDataObject
    {
        $translated = [];
        (new Pool($this->client, $this->createTranslationRequests($data), [
            'concurrency' => $this->concurrentRequests,
            'fulfilled' => function (
                ResponseInterface $response,
                int|string $key,
            ) use (&$translated): void {
                $translated[$key] = json_decode(
                    $response->getBody()->getContents(),
                    true,
                    flags: JSON_THROW_ON_ERROR,
                )['responseData']['translatedText']
                    ?? throw new RuntimeException('Invalid JSON payload');
            },
            'rejected' => function (Throwable $e) {
                throw new RuntimeException('Translation failed', 0, $e);
            },
        ]))->promise()->wait();

        $data = clone $data;
        $data->setFieldsFromArray($translated);

        return $data;
    }

    /**
     * @return Generator<array-key, RequestInterface>
     */
    private function createTranslationRequests(H5PTranslationDataObject $data): Generator
    {
        foreach ($data->getDocument() as $key => $value) {
            yield $key => new Request(
                'POST',
                (new Uri('translateText'))->withQuery(http_build_query([
                    'stilmal' => $this->style,
                    'q' => $value,
                ])),
            );
        }
    }
}
