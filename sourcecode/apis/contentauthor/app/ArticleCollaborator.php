<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleCollaborator extends Model
{
    use HasFactory;

    protected  $fillable = ['email'];
}
