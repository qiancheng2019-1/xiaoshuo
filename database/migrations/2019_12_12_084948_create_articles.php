<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 64)->nullable(false)->default('');
            $table->string('pinyin', 128)->nullable(false)->default('')->comment('拼音/远程爬虫id');
            $table->string('author', 64)->nullable(false)->default('')->comment('作者');
            $table->string('url', 128)->nullable(false)->default('')->comment('源');
            $table->string('info', 512)->nullable(false)->default('')->comment('简介');
            $table->string('thumb', 128)->nullable(false)->default('')->comment('封面/缩略图');
            $table->bigInteger('category_id')->nullable(false)->default(0)->comment('分类id');
            $table->string('category', 64)->nullable(false)->default('')->comment('分类');
            $table->bigInteger('pid')->nullable(false)->default(0)->comment('采集节点');
            $table->string('tags', 255)->nullable(false)->default('')->comment('标签相关');
            $table->string('last_chapter', 255)->nullable(false)->default('')->comment('最新章节');
            $table->bigInteger('last_chapter_id')->nullable(false)->default(0)->comment('最新章节ID');
            $table->tinyInteger('is_full')->nullable(false)->default(0)->comment('是否完本');
            $table->tinyInteger('is_push')->nullable(false)->default(0)->comment('推送标记');
            $table->tinyInteger('is_original')->nullable(false)->default(0)->comment('原创');
            $table->tinyInteger('status')->nullable(false)->default(0)->comment('状态');
            $table->timestamps();
        });
        Schema::table('articles',function (Blueprint $table){
            $table->index('category_id');
            $table->index('pid');
//            $table->unique('url');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
