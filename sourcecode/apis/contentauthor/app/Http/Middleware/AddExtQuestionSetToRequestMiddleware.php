<?php

namespace App\Http\Middleware;

use Closure;
use App\SessionKeys;

/**
 * This middleware is used to test the pre-filling of the question set with
 * values from the LTI request.
 *
 * Will only work in non-production environments with
 * `config('feature.add-ext-question-set-to-request', true)`
 */
class AddExtQuestionSetToRequestMiddleware
{
    public const QUESTION_SET_DATA = [
        'title' => 'QuestionSet created at 2018-07-10T07:26:24+02:00',
        'sharing' => false,
        'license' => 'BY',
        'authId' => 'some-auth-id',
        'tags' => ['tag1', 'tag2'],
        'questions' => [
            [
                'text' => 'How are you?',
                'answers' => [
                    [
                        'text' => 'Fine',
                        'correct' => true,
                        'order' => 0,
                    ],
                    [
                        'text' => 'So, so...',
                        'correct' => false,
                        'order' => 1,
                    ],
                    [
                        'text' => 'Horrible!',
                        'correct' => false,
                        'order' => 2,
                    ],
                ],
                'order' => 0,
            ],
            [
                'text' => 'Where are you?',
                'answers' => [
                    [
                        'text' => 'At work',
                        'correct' => true,
                        'order' => 0,
                    ],
                    [
                        'text' => 'Home',
                        'correct' => false,
                        'order' => 1,
                    ],
                    [
                        'text' => 'On the bus',
                        'correct' => false,
                        'order' => 2,
                    ],
                ],
                'order' => 1,
            ],
        ],
    ];

    public function __construct(
        private string $environment,
        private bool $enabled,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        if ($this->environment !== 'production' && $this->enabled) {
            $request->getSession()->set(
                SessionKeys::EXT_QUESTION_SET,
                json_encode(self::QUESTION_SET_DATA, JSON_THROW_ON_ERROR),
            );
        }

        return $next($request);
    }
}
