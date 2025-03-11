<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

use function strtolower;

trait HasUlidsFromCreationDate
{
    use HasUlids;

    public function newUniqueId(): string
    {
        $timestamp = $this->{$this->getCreatedAtColumn()} ?? Date::now();

        return strtolower((string) Str::ulid($timestamp));
    }
}
