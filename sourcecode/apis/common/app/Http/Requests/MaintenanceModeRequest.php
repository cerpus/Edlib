<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceModeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'enabled' => 'required|bool',
        ];
    }
}
