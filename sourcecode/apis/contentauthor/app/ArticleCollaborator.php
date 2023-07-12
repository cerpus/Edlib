<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property  string $article_id
 * @property string $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

class ArticleCollaborator extends Model
{
    use HasFactory;

    protected $fillable = ['email'];
}
