<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiH5PQuestionsetRequest extends FormRequest
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
            'title' => 'required|string',
            'license' => 'required',
            'authId' => 'required',
            'questions' => 'required|array',
            'questions.*.text' => 'required',
            'questions.*.type' => 'required',
            'questions.*.answers' => 'required|array',
            'questions.*.answers.*.text' => 'required',
            'questions.*.answers.*.correct' => 'required|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->isJson()) {
                $validator->errors()->add('json', 'The request only handles json');
            }
        });
    }
}
