<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\LtiTool;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Launch the tool if it's the only tool, and it does not have any non-admin extras
 */
final readonly class LaunchCreateIfSingleTool
{
    public function handle(Request $request, Closure $next): Response
    {
        if (LtiTool::count() === 1) {
            $ltiTool = LtiTool::first();
            if ($ltiTool?->extras()->forAdmins(false)->count() === 0) {
                return redirect(
                    route('content.launch-creator', $ltiTool),
                );
            }
        }

        return $next($request);
    }
}
