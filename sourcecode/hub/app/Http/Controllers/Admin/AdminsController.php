<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\GrantAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

use function redirect;
use function response;

final readonly class AdminsController
{
    public function index(): Response
    {
        $users = User::where('admin', true)->paginate();

        return response()->view('admin.admins.index', [
            'users' => $users,
        ]);
    }

    public function add(GrantAdminRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        $user = User::where('email', $email)
            ->where('email_verified', true)
            ->firstOrFail();
        $user->admin = true;
        $user->save();

        return redirect()->back();
    }

    public function remove(User $user): RedirectResponse
    {
        $user->admin = false;
        $user->save();

        return redirect()->back();
    }
}
