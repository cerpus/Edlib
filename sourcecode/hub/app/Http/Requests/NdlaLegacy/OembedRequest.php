<?php

declare(strict_types=1);

namespace App\Http\Requests\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use BadMethodCallException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use function parse_str;
use function parse_url;
use function preg_match;
use function preg_quote;

use const PHP_URL_QUERY;

class OembedRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(NdlaLegacyConfig $config): array
    {
        return [
            'url' => ['required', 'regex:' . $this->getResourceRegex($config)],
            'format' => ['sometimes', Rule::in(['json', 'xml'])],
        ];
    }

    public function getResourceId(NdlaLegacyConfig $config): string
    {
        $url = $this->validated('url');

        if (!preg_match($this->getResourceRegex($config), $url, $matches)) {
            throw new BadMethodCallException('No valid URL');
        }

        return $matches[1];
    }

    public function getUrlLocale(): string|null
    {
        $qs = parse_url($this->validated('url'), PHP_URL_QUERY) ?: '';
        parse_str($qs, $query);

        if (!is_string($query['locale'] ?? null)) {
            return null;
        }

        return $query['locale'];
    }

    private function getResourceRegex(NdlaLegacyConfig $config): string
    {
        return '@^https?://' . preg_quote($config->getDomain(), '!') .
            '/resource/([^/?#]+)(?=[?#]|$)@';
    }
}
