<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use Random\Randomizer;

use function bin2hex;
use function is_string;

final readonly class SessionScope
{
    public const TOKEN_PARAM = 'session_scope';

    private const RANDOM_BYTES = 8;

    public function __construct(
        private Container $container,
        private SessionManager $sessionManager,
        private Randomizer $randomizer,
    ) {}

    public function isScoped(Request $request): bool
    {
        return $request->attributes->has('session-scope-outer');
    }

    /**
     * Start a scoped session for the request.
     */
    public function start(Request $request): Session
    {
        if ($this->isScoped($request)) {
            // already scoped, return the existing session
            return $request->session();
        }

        $outer = $request->session();
        $token = $this->getToken($request) ?? $this->generateToken();
        $id = $outer->get("session-scope-ids.$token");

        $session = new Store($outer->getName(), $outer->getHandler(), $id);
        $this->setSession($request, $session);
        $session->start();

        $outer->put("session-scope-ids.$token", $session->getId());
        $request->attributes->set('session-scope-outer', $outer);
        $request->attributes->set('session-scope-token', $token);

        return $session;
    }

    /**
     * Start a scoped session using a token from a previous request, if any.
     * Otherwise, the session is not scoped.
     */
    public function resume(Request $request): void
    {
        if ($this->isScoped($request)) {
            return;
        }

        $token = $this->getToken($request);

        if ($token === null) {
            return;
        }

        $this->start($request);
    }

    /**
     * Restore the previous "global" session.
     */
    public function restore(Request $request): void
    {
        if (!$this->isScoped($request)) {
            return;
        }

        $request->session()->save();

        $oldSession = $request->attributes->get('session-scope-outer');
        assert($oldSession instanceof Session);

        $this->setSession($request, $oldSession);

        $request->attributes->remove('session-scope-outer');
        $request->attributes->remove('session-scope-token');
    }

    public function getToken(Request $request): string|null
    {
        $token = $request->attributes->get('session-scope-token');

        if ($token !== null) {
            return $token;
        }

        $id = $request->query(self::TOKEN_PARAM);

        if (is_string($id) && $this->isTokenValid($id)) {
            return $id;
        }

        return null;
    }

    private function generateToken(): string
    {
        return bin2hex($this->randomizer->getBytes(self::RANDOM_BYTES));
    }

    private function isTokenValid(string $token): bool
    {
        return strlen($token) === self::RANDOM_BYTES * 2 &&
            preg_match('/^[a-z0-9]+$/', $token);
    }

    private function setSession(Request $request, Session $session): void
    {
        $request->setLaravelSession($session);

        // The container instance of the session must also be replaced, because
        // Laravel naively assumes there would never be other sessions other
        // than its own.
        $this->container->instance('session.store', $session);

        // Likewise, the SessionManager singleton "helpfully" caches the session
        // instance it creates, and some session accesses happen through this
        // (e.g. csrf_token()). There seems to be no way around other than
        // changing a protected property, however.
        (function () use ($session) {
            /** @var SessionManager $this */
            $this->drivers[$this->getDefaultDriver()] = $session;
        })->call($this->sessionManager);
    }
}
