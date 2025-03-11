<?php

declare(strict_types=1);

namespace App\Http\Requests\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use BadMethodCallException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use function preg_match;
use function preg_quote;

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

    private function getResourceRegex(NdlaLegacyConfig $config): string
    {
        return '@^https?://' . preg_quote($config->getDomain(), '!') .
            '/resource/([^/?#]+)(?=[?#]|$)@';
    }
}
