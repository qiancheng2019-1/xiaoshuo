<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_comment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('article_id')->nullable(false)->default(0)->index();
            $table->unsignedBigInteger('comment_id')->nullable(false)->default(0)->index();
            $table->unsignedBigInteger('user_id')->nullable(false)->default(0)->index();
            $table->string('nickname', 128)->nullable(false)->default('');
            $table->string('comment', 2048)->nullable(false)->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_comment');
    }
}
