<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLtiResourceRequest extends FormRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:300'],
            'launch_url' => ['required', 'url'],
            'consumer_key' => ['required', 'string'],
            'consumer_secret' => ['required', 'string'],
        ];
    }
}
