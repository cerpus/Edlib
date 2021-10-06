<?php

namespace App\Http\Requests;

use App\Content;
use App\Game;
use App\H5PContent;
use App\Libraries\Games\Millionaire\Millionaire;
use App\Libraries\H5P\Packages\QuestionSet;
use App\Rules\canPublishContent;
use App\Rules\LicenseContent;
use App\Rules\shareContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\QuestionSet as QuestionSetModel;

class ApiQuestionsetRequest extends FormRequest
{
    private $selectedPresentation;
    private $contentModel;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; //$this->session()->has("userId");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $jsonData = $this->filled('questionSetJsonData') ? json_decode($this->get('questionSetJsonData'), true) : [];
        $selectedPresentation = !empty($jsonData['selectedPresentation']) ? $jsonData['selectedPresentation'] : null;
        $this->selectedPresentation = $selectedPresentation;
        $this->request->add(['selectedPresentation' => $selectedPresentation]);

        switch ($selectedPresentation) {
            case QuestionSet::$machineName:
                $rules = [
                    'cards' => 'required|array|min:1',
                    'cards.*.question' => 'required|array',
                    'cards.*.question.text' => 'required|string',
                    'cards.*.question.image' => 'present|array|nullable',
                    'cards.*.answers' => 'required_with:cards.*.question.text|array|min:4',
                    'cards.*.answers.*.isCorrect' => 'required_with:cards.*.question.text|boolean',
                    'cards.*.answers.*.answerText' => 'required_with:cards.*.question.text|string',
                    'cards.*.answers.*.image' => 'present|array|nullable',
                ];
                $this->contentModel = $this->route()->parameter('id') ?? new H5PContent();
                break;
            case Millionaire::$machineName:
                $rules = [
                    'cards' => 'required|array|size:15',
                    'cards.*.question' => 'required|array',
                    'cards.*.question.text' => 'required|string',
                    'cards.*.question.image' => 'present|array|nullable',
                    'cards.*.answers' => 'required_with:cards.*.question.text|array|size:4',
                    'cards.*.answers.*.isCorrect' => 'required_with:cards.*.question.text|boolean',
                    'cards.*.answers.*.answerText' => 'required_with:cards.*.question.text|string',
                    'cards.*.answers.*.image' => 'present|array|nullable',
                ];
                $this->contentModel = $this->route()->parameter('game') ?? new Game();
                break;
            default:
                $rules = [
                    'cards' => 'required|array|min:1',
                    'cards.*.question' => 'required|array',
                    'cards.*.question.text' => 'required|string',
                    'cards.*.question.image' => 'present|array|nullable',
                    'cards.*.answers' => 'required_with:cards.*.question.text|array',
                    'cards.*.answers.*.isCorrect' => 'required_with:cards.*.question.text|boolean',
                    'cards.*.answers.*.answerText' => 'required_with:cards.*.question.text|string',
                    'cards.*.answers.*.image' => 'present|array|nullable',
                ];
                $this->contentModel = $this->route()->parameter('questionset') ?? new QuestionSetModel();
        }

        return array_merge($this->getCommonRules(), $rules);
    }

    public function withValidator($validator)
    {
        switch ($this->get('selectedPresentation')) {
            case Millionaire::$machineName:
                $validator->after(function (Validator $validator) {
                    $errors = Millionaire::customValidation($validator->getData());
                    if ($errors !== true) {
                        foreach ($errors as $key => $error) {
                            $validator->errors()->add($key, $error);
                        }
                    }
                });
                break;
        }
        $jsonData = $this->filled('questionSetJsonData') ? json_decode($this->get('questionSetJsonData'), true) : [];
        $this->request->add($jsonData);
    }

    private function getCommonRules()
    {
        return [
            'sharing' => 'sometimes|boolean',
            'license' => ['required', 'string', app(LicenseContent::class)],
            'questionSetJsonData' => 'sometimes|json',
            'title' => 'required|string',
            'tags' => 'present|array',
            'isPublished' => [Rule::requiredIf(Content::isDraftLogicEnabled()), 'boolean', new canPublishContent($this->contentModel, $this)],
            'share' => ['sometimes', new shareContent(), new canPublishContent($this->contentModel, $this, 'list')],
        ];
    }

    public function messages()
    {
        switch ($this->selectedPresentation) {
            case Millionaire::$machineName:
                return [
                    'title.required' => trans('questions.error.title'),
                    'cards.required' => trans('questions.error.no-cards'),
                    'cards.size' => trans('game.error.exactly-15'),
                    'cards.*.question.text.required' => trans('questions.error.question-missing-text'),
                    'cards.*.answers.size' => trans('game.error.exactly-4'),
                    'cards.*.answers.*.answerText.required_with' => trans('questions.error.answer-missing-text'),
                ];
                break;

            case QuestionSet::$machineName:
                return [
                    'title.required' => trans('questions.error.title'),
                    'cards.required' => trans('questions.error.no-cards'),
                    'cards.*.question.text.required' => trans('questions.error.question-missing-text'),
                    'cards.*.answers.*.answerText.required_with' => trans('questions.error.answer-missing-text'),
                ];

                break;

            default:
                return [
                    'title.required' => trans('questions.error.title'),
                    'cards.required' => trans('questions.error.no-cards'),
                    'cards.*.question.text.required' => trans('questions.error.question-missing-text'),
                    'cards.*.answers.*.answerText.required_with' => trans('questions.error.answer-missing-text'),
                ];

                break;
        }
    }

    public function validationData()
    {
        $all = parent::validationData();
        $jsonData = $this->filled('questionSetJsonData') ? json_decode($this->get('questionSetJsonData'), true) : [];
        return array_merge($all, $jsonData);
    }
}
