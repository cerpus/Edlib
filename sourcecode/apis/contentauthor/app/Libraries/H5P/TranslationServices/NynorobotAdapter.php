<?php

declare(strict_types=1);

namespace App\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use DOMElement;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Masterminds\HTML5;
use RuntimeException;

final readonly class NynorobotAdapter implements TranslationServiceInterface
{
    public const STYLE_MODERATE = 'Moderat nynorsk';
    public const STYLE_RADICAL = 'Radikal nynorsk';
    public const STYLE_CONSERVATIVE = 'Konservativ nynorsk';
    public const STYLE_INTERNAL_4 = 'Intern nynorsk 4';

    /**
     * @param self::STYLE_* $style
     */
    public function __construct(
        private ClientInterface $client,
        private string $style,
    ) {}

    public function getSupportedLanguages(): array|null
    {
        return [
            'nob' => ['nno'],
        ];
    }

    public function translate(string $toLanguage, H5PTranslationDataObject $data): H5PTranslationDataObject
    {
        $html = $this->convertFieldsToHtml($data->getFields());

        try {
            $response = $this->client->request('POST', 'translateNHtml', [
                'headers' => [
                    'Accept' => 'text/html',
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $html,
                        'filename' => 'tmp.html',
                        'headers' => [
                            'Content-Type' => 'text/html',
                        ],
                    ],
                ],
                'query' => [
                    'stilmal' => $this->style,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Error from translation service', 0, $e);
        }

        $translated = $this->extractTranslationFromHtml(
            $response->getBody()->getContents(),
        );

        return new H5PTranslationDataObject($translated, 'nno');
    }

    /**
     * Convert fields to an HTML document, with each path represented as its own
     * div tag.
     *
     * Example:
     *
     * ```html
     * <html><body>
     * <div edlib-translation-path="foo"><p>One fragment</p></div>
     * <div edlib-translation-path="bar"><p>Another fragment</p></div>
     * </body></html>
     * ```
     *
     * @param string[] $fields
     */
    private function convertFieldsToHtml(array $fields): string
    {
        $html5 = new HTML5(['disable_html_ns' => true]);
        $dom = $html5->parse('<html><body></body></html>');
        $root = $dom->getElementsByTagName('body')->item(0);
        assert($root instanceof DOMElement);

        foreach ($fields as $path => $value) {
            $node = $dom->createElement('div');
            $node->setAttribute('edlib-translation-path', (string) $path);
            $node->appendChild($dom->importNode($html5->parseFragment($value), true));
            $root->appendChild($node);
        }

        return $dom->saveHTML();
    }

    /**
     * Convert the fields from HTML converted by {@link self::convertFieldsToHtml()}
     * and translated by the translation service back to an associative
     * `path => html` array.
     */
    private function extractTranslationFromHtml(string $html): array
    {
        $fields = [];
        $dom = (new HTML5(['disable_html_ns' => true]))->parse($html);
        $body = $dom->getElementsByTagName('body')->item(0);

        if (!$body instanceof DOMElement) {
            throw new RuntimeException('The HTML was expected to contain a <body> tag');
        }

        $node = $body->firstElementChild;
        do {
            $path = $node->getAttribute('edlib-translation-path');
            $value = '';
            foreach ($node->childNodes as $subNode) {
                $value .= $dom->saveHTML($subNode);
            }
            $fields[$path] = $value;
        } while ($node = $node->nextElementSibling);

        return $fields;
    }
}
