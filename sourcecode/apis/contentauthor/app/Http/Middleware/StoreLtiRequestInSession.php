<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Lti\Lti;
use Closure;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final readonly class StoreLtiRequestInSession
{
    public function __construct(private Lti $lti) {}

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $ltiRequest = $this->lti->getRequest($request);

        if ($ltiRequest) {
            $listEntry = $request->get('redirectToken');

            if (!$listEntry) {
                $listEntry = Uuid::uuid4()->toString();
                $request->request->add(['redirectToken' => $listEntry]);
            }

            $request->session()->put('lti_requests.' . $listEntry, $ltiRequest);
        }

        return $next($request);
    }
}
