<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\ContentLockedException;
use App\Models\Content;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function response;

final class LockContent
{
    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('lti.ext_edlib3_copy_before_save')) {
            // no locking when copying
            return $next($request);
        }

        $content = $request->route('content');
        assert($content instanceof Content);

        if (!$content->isLocked() && $request->query('refreshed')) {
            // Remove the parameter so the user can refresh again
            return new RedirectResponse($request->fullUrlWithoutQuery('refreshed'));
        }

        $user = $request->user();
        assert($user instanceof User);

        try {
            $content->acquireLock($user);
        } catch (ContentLockedException $e) {
            if (!$request->query('refreshed')) {
                // Even though the lock is released upon unloading the editor,
                // the unload event hasn't fired until *after* the next page is
                // loaded. Therefore, the content is locked upon reloading the
                // page. To work around this problem, we fire an HTML redirect,
                // which will force the event to fire, and, if unlocked by then,
                // redirect back to the editor. (HTTP redirects do not work for
                // this purpose)
                return response()->view('lti.redirect', [
                    'url' => $request->url(),
                    'method' => 'GET',
                    'parameters' => [
                        ...$request->query->all(),
                        'refreshed' => true,
                    ],
                ]);
            }

            return response()->view('content.locked-error', [
                'lock' => $e->getContent()->getActiveLock(),
            ], status: Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
