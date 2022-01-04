<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetResourceCollaboratorsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'context' => ['required', 'string'],
            'resourceIds' => ['array'],
            'resourceIds.*' => ['required_with:resourceIds', 'string', "distinct"],
            'tenantIds' => ['array'],
            'tenantIds.*' => ['required', 'string', "distinct"],
            'externalResources' => ['array'],
            'externalResources.*.systemName' => ['required_with:externalResources', 'string'],
            'externalResources.*.resourceId' => ['required_with:externalResources', 'string'],
        ];
    }
}
