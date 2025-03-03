<?php

namespace App\Auth\Guards;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EdlibGuard implements StatefulGuard
{
    protected ?GenericUser $user = null;
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function check(): bool
    {
        return (bool) $this->user();
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function id(): ?int
    {
        $user = $this->user();
        return $user->id ?? null;
    }

    public function setUser(?Authenticatable $user): self
    {
        $this->user = $user;
        Session::put('user', $user);
        return $this;
    }

    public function validate(array $credentials = []): bool
    {
        throw new \BadMethodCallException('Unexpected method call');
    }

    public function hasUser(): bool
    {
        return Session::has('user');
    }

    public function user(): ?GenericUser
    {
        return Session::get('user');
    }

    public function logout()
    {
        if ($this->user) {
            $this->setUser(null);
        }
    }

    public function attempt(array $credentials = [], $remember = false): bool
    {
        return false;
    }

    public function once(array $credentials = []): bool
    {
        return false;
    }

    public function login(Authenticatable $user, $remember = false)
    {
        if (!($user instanceof GenericUser)) {
            throw new \TypeError("Login with EdlibGuard must pass a user of GenericUser type");
        }

        Session::put('user', $user);
        $this->user = $user;
    }

    public function loginUsingId($id, $remember = false): bool
    {
        return false;
    }

    public function onceUsingId($id): bool
    {
        return false;
    }

    public function viaRemember(): bool
    {
        return false;
    }
}
