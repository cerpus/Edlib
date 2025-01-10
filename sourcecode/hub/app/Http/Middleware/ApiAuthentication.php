<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class ApiAuthentication
{
    public function __construct(private AuthManager $authManager) {}

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::firstWhere('id', $request->getUser());

        if ($user?->validateApiSecret($request->getPassword() ?? '')) {
            $this->authManager->onceUsingId($user->email);

            return $next($request);
        }

        throw new UnauthorizedHttpException(
            challenge: 'Basic',
            message: 'Unknown API key or invalid secret',
        );
    }
}
