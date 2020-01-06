<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesChapter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_chapter', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('title_id')->index()->comment('article_id');
            $table->unsignedBigInteger('chapter_id')->unique()->comment('章节ID');
            $table->string('chapter_name',255)->comment('章节名称');
//            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_chapter');
    }
}
