<?php

namespace App\Http\Requests;

class LinksRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'linkType' => 'required',
            'linkUrl' => 'required_if:linkType,external_link',
            'linkMetadata' => 'json',
        ];
    }
}
