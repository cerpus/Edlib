<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Content;
use App\Models\User;
use Illuminate\Http\Request;

use function request;

class ContentPolicy
{
    public function __construct(private Request $request)
    {
    }

    public function use(User|null $user, Content $content): bool
    {
        return $this->request->hasPreviousSession() &&
            $this->request->session()->has('lti');
    }

    public function view(User|null $user, Content $content): bool
    {
        if ($user?->admin) {
            return true;
        }

        return $content->latestPublishedVersion()->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function edit(User $user, Content $content): bool
    {
        if ($user->admin) {
            return true;
        }

        return $content->users()->where('id', $user->id)->exists();
    }

    public function copy(User $user, Content $content): bool
    {
        return true;
    }
}
