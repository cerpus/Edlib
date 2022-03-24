<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class H5pUpgradeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'h5p_file' => ['required', 'file'],
            'h5p_upgrade_only' => ['sometimes', 'accepted'],
            'h5p_disable_file_check' => ['sometimes', 'accepted'],
        ];
    }
}
