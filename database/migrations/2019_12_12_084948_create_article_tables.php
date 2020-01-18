<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleTables extends Migration
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
            $table->string('title', 64)->nullable(false)->default('')->index();
            $table->string('author', 64)->nullable(false)->default('')->comment('作者')->index();
            $table->string('url', 128)->nullable(false)->default('')->comment('爬虫源');
            $table->string('info', 512)->nullable(false)->default('')->comment('简介');
            $table->string('thumb', 128)->nullable(false)->default('')->comment('封面/缩略图');
            $table->bigInteger('category_id')->nullable(false)->default(0)->comment('分类id')->index();
            $table->string('category', 64)->nullable(false)->default('')->comment('分类');
            $table->string('last_chapter', 255)->nullable(false)->default('')->comment('最新章节');
            $table->bigInteger('last_chapter_id')->nullable(false)->default(0)->comment('最新章节ID');
            $table->tinyInteger('is_full')->nullable(false)->default(0)->comment('是否完本');
            $table->tinyInteger('is_push')->nullable(false)->default(0)->comment('是否推送');
            $table->integer('font_count')->nullable(false)->default(0)->comment('字数统计');
            $table->tinyInteger('status')->nullable(false)->default(0)->comment('状态');
            $table->timestamps();
        });
        Schema::table('articles',function (Blueprint $table){
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('articles_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 64)->nullable(false)->default('');
            $table->string('title', 64)->nullable(false)->default('')->comment('标题');
            $table->integer('page')->nullable(false)->default(0)->comment('爬虫页码记录');
            $table->integer('order')->nullable(false)->default(0)->comment('排序');
            $table->integer('status')->nullable(false)->default(0)->comment('状态')->index();
            $table->timestamps();
        });

        Schema::create('articles_chapter', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('title_id')->index()->comment('article_id');
            $table->unsignedBigInteger('chapter_id')->unique()->comment('章节ID');
            $table->string('chapter_name',255)->comment('章节名称');
            $table->timestamps();
        });

        Schema::create('articles_views', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id')->primary();
            $table->integer('week_views')->nullable(false)->default(0)->comment('周点击')->index();
            $table->integer('month_views')->nullable(false)->default(0)->comment('月点击')->index();
            $table->integer('total_views')->nullable(false)->default(0)->comment('总点击')->index();
            $table->smallInteger('week')->nullable(false)->default(0)->comment('最后统计周');
            $table->smallInteger('month')->nullable(false)->default(0)->comment('最后统计月');
            $table->timestamps();
        });

        Schema::create('config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 64)->nullable(false)->default('');
//            $table->string('name', 64)->nullable(false)->default('');
//            $table->string('desc', 128)->nullable(false)->default('');
            $table->string('value', 512)->nullable(false)->default('');
            $table->timestamps();
        });

        Schema::create('ad', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 64)->nullable(false)->default('')->unique();
            $table->string('name', 64)->nullable(false)->default('');
            $table->string('value', 2048)->nullable(false)->default('');
            $table->timestamps();
        });

        Schema::create('ad_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ip',16);
            $table->char('key',16)->index();
            $table->char('method',8)->index();
            $table->timestamps();
        });

        Schema::create('users_collects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable(false)->default(0)->index();
            $table->unsignedBigInteger('article_id')->nullable(false)->default(0)->index();
            $table->unsignedBigInteger('last_chapter_id')->nullable(false)->default(0);
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('articles_category');
        Schema::dropIfExists('articles_chapter');
        Schema::dropIfExists('articles_views');
        Schema::dropIfExists('users_collects');
        Schema::dropIfExists('config');
        Schema::dropIfExists('ad');
        Schema::dropIfExists('ad_log');
    }
}
