<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\LtiPlatform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LogicException;

final class UpdateLtiPlatformRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $input = $this->getInputSource();

        if (!$input->has('enable_sso')) {
            $input->set('enable_sso', false);
        }

        if (!$input->has('authorizes_edit')) {
            $input->set('authorizes_edit', false);
        }
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        $platform = $this->route('platform');

        if (!$platform instanceof LtiPlatform) {
            throw new LogicException('Should not happen');
        }

        return [
            'name' => [
                'required',
                'max:100',
                Rule::unique(LtiPlatform::class, 'name')->ignore($platform),
            ],
            'enable_sso' => ['boolean'],
            'authorizes_edit' => ['boolean'],
        ];
    }
}
