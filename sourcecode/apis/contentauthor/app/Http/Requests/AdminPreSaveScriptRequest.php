<?php

namespace App\Http\Requests;

class AdminPreSaveScriptRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'machineName' => 'required|string',
            'majorVersion' => 'required|int',
            'minorVersion' => 'required|int',
        ];
    }
}
