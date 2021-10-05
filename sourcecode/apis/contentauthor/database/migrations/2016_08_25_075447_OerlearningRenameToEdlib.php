<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Article;

class OerlearningRenameToEdlib extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $articles = Article::where("content", "like", '%oerlearning_resource%')->get();
        foreach ($articles as $article) {
            $article->content = str_replace("oerlearning_resource", "edlib_resource", $article->content);
            $article->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $articles = Article::where("content", "like", '%edlib_resource%')->get();
        foreach ($articles as $article) {
            $article->content = str_replace("edlib_resource", "oerlearning_resource", $article->content);
            $article->save();
        }
    }
}
