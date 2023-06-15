<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static BehaviorSettingsDataObject create($attributes = null)
 */
class BehaviorSettingsDataObject extends BaseDataObject
{
    use CreateTrait;

    /**
     * Setting to override the logic to retry a H5P in the learners view.
     * True = enables retry
     * False = disables retry
     * Null = use value set on the resource(default)
     *
     * @var bool|null
     */
    public $enableRetry;

    /**
     * Setting to override the logic to enable instant checking of answers in the learners view
     * True = enables auto checking of answers
     * False = disables auto checking of answers
     * Null = use value set on the resource(default)

     * @var bool|null
     */
    public $autoCheck;

    /**
     * Setting to preset a mode for behavior settings.
     *
     * Supported modes are 'exam' and 'score'
     *
     * Mode 'exam' equals {
     *      enableRetry: false,
     *      autoCheck: false
     * }
     *
     * Mode 'score' equals {
     *      enableRetry: true,
     *      autoCheck: false
     * }
     */
    public $presetmode;

    /**
     * Setting to override the logic to enable or disable showint the solution in the learners view
     * True = enables showing of solution
     * False = disables showing of solution
     * Null = use value set on the resource(default)
     * @var bool|null
     */
    public $showSolution;

    /**
     * Setting to skip including the previous answers when loading a resource
     * True = include answers
     * False = exclude answers
     * @var bool
     */
    public $includeAnswers = true;

    /**
     * Setting to be able to override the setting of hide/show the summary
     * True = summary is shown
     * False = summari is suppressed
     */
    public $showSummary;

    public static $rules = [
        'enableRetry' => 'boolean|nullable',
        'autoCheck' => 'boolean|nullable',
        'presetmode' => ['regex:/^(exam|score)$/', 'nullable'],
        'showSolution' => 'boolean|nullable',
        'includeAnswers' => 'boolean|nullable',
        'showSummary' => 'boolean|nullable',
    ];
}
