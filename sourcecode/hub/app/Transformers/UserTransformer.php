<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

final class UserTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'admin' => $user->admin,
            'theme' => $user->theme,
            'created_at' => $user->created_at?->format('c'),
            'updated_at' => $user->updated_at?->format('c'),
            'locale' => $user->locale,
            'debug_mode' => $user->debug_mode,
        ];
    }
}
