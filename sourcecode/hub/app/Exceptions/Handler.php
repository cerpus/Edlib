<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function array_filter;
use function http_build_query;
use function is_string;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Redirect to the LTI tool consumer in accordance with the LTI spec
        $this->renderable(function (LtiException $e, Request $request) {
            $session = $request->session();
            $type = $session->get('lti.lti_message_type');

            $redirectUrl = match ($type) {
                'basic-lti-launch-request' => $session->get('lti.launch_presentation_return_url'),
                'ContentItemSelectionRequest' => $session->get('lti.content_item_return_url'),
                default => null,
            };

            if (!is_string($redirectUrl)) {
                return null;
            }

            $redirectUrl .= '?' . http_build_query(array_filter([
                'lti_errorlog' => $e->getMessage(),
                'lti_errormsg' => $e->getVisibleMessage(),
            ]));

            return new RedirectResponse($redirectUrl);
        });
    }
}
