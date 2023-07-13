<?php

namespace App\Http\Controllers\Admin;

use App\Administrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $adminUsers = Administrator::select('id', 'username', 'name')->where('id', '<>', 1)->get();

        return view('admin.admin-users.index')->with(compact('adminUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'name' => 'required|min:3|max:255',
                'username' => [
                    'required',
                    'min:3',
                    'max:190',
                    'unique:administrators,username',
                    Rule::notIn(['admin']), // The unique constraint should take care of this...but to make doubly sure
                ],
                'password' => 'required|alpha_num|between:18,255',
            ]
        );

        $newUser = new Administrator();
        $newUser->username = $validated['username'];
        $newUser->name = $validated['name'];
        $newUser->password = Hash::make($validated['password']);
        $newUser->save();

        $request->session()->flash('message', "User {$request->name} created!");

        return redirect(route('admin-users.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        if ((int)$id === (int)Auth::user()->id) {
            request()->session()->flash('message', "You can not delete yourself!");
        } else {
            $user = Administrator::findOrFail($id);
            $user->delete();

            request()->session()->flash('message', "{$user->name} deleted!");
        }

        return redirect(route('admin-users.index'));
    }
}
