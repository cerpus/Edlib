<?php

namespace App\Rules;

use App\Content;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class canPublishContent implements Rule
{

    /** @var Content */
    private $content;

    /** @var Request */
    private $request;

    private $goal;

    /**
     * Create a new rule instance.
     *
     * @param Content $content
     * @param Request $request
     * @param string $goal
     */
    public function __construct(Content $content, Request $request, $goal = 'publish')
    {
        $this->content = $content;
        $this->request = $request;
        $this->goal = $goal;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !Content::isUserPublishEnabled() || !!$value === false || $this->content->canPublish($this->request);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->goal === 'list') {
            return trans('validation.custom.isPublished.cant-list');
        }
        return trans('validation.custom.isPublished.cant-publish');
    }
}
