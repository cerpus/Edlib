<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasUuids;
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'prefix',
        'name',
    ];

    public static function findOrCreateFromString(string $tag): self
    {
        ['prefix' => $prefix, 'name' => $name] = self::parse($tag);

        return self::firstOrCreate([
            'prefix' => strtolower($prefix),
            'name' => strtolower($name),
        ]);
    }

    public static function extractVerbatimName(string $tag): string
    {
        return self::parse($tag)['name'];
    }

    /**
     * @return array{prefix: string, name: string}
     */
    public static function parse(string $tag): array
    {
        $parts = explode(':', $tag, 2);

        return [
            'prefix' => isset($parts[1]) ? $parts[0] : '',
            'name' => $parts[1] ?? $parts[0],
        ];
    }
}
