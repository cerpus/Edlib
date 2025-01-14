<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ContentRole;
use App\Models\Context;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use function array_map;

final class ContentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(Gate $gate): array
    {
        return [
            'shared' => ['sometimes', 'boolean'],

            'contexts' => [
                Rule::exists(Context::class, 'name'),
            ],

            'created_at' => [
                Rule::prohibitedIf(fn() => $gate->denies('admin')),
                'sometimes',
                'date',
            ],

            'deleted_at' => [
                Rule::prohibitedIf(fn() => $gate->denies('admin')),
                'sometimes',
                'date',
            ],

            'roles.*.user' => [
                Rule::prohibitedIf(fn() => $gate->denies('admin')),
                Rule::exists(User::class, 'id'),
            ],

            'roles.*.role' => [
                Rule::prohibitedIf(fn() => $gate->denies('admin')),
                Rule::enum(ContentRole::class),
                'required_with:roles.*.user',
            ],

            'tags.*' => ['string'],
        ];
    }

    /**
     * @return array<int, Context>
     */
    public function getContexts(): array
    {
        return array_map(
            fn(string $name) => Context::where('name', $name)->firstOrFail(),
            $this->validated('contexts', []),
        );
    }

    /**
     * @return array<int, array{user: User, role: ContentRole}>
     */
    public function getRoles(): array
    {
        $roles = $this->validated('roles', []);

        return array_map(fn(array $role) => [
            'user' => User::where('id', $role['user'])->firstOrFail(),
            'role' => ContentRole::from($role['role']),
        ], $roles);
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->validated('tags', []);
    }
}
