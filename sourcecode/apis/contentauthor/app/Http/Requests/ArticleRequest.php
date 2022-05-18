<?php

namespace App\Http\Requests;

use App\Article;
use App\Rules\canPublishContent;
use App\Rules\LicenseContent;
use App\Rules\shareContent;
use Illuminate\Validation\Rule;

class ArticleRequest extends Request
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
        $article = $this->route()->parameter('article') ?? new Article();
        return [
            'title' => 'required|min:1|max:255',
            'content' => 'required|filled',
            'origin' => 'nullable|min:1,max:1000',
            'originators' => 'nullable|array',
            'originators.*.name' => 'required|min:1|max:1000',
            'originators.*.role' => 'required|in:Source,Supplier,Writer',
            'isPublished' => [Rule::requiredIf($article::isUserPublishEnabled()), 'boolean', new canPublishContent($article, $this)],
            'share' => ['sometimes', new shareContent(), new canPublishContent($article, $this, 'list')],
            'license' => [Rule::requiredIf($this->input('share') === 'share'), 'string', app(LicenseContent::class)],
        ];
    }
}
