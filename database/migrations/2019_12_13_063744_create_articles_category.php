<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('name', 64)->nullable(false)->default('');
            $table->char('title', 64)->nullable(false)->default('')->comment('标题');
            $table->char('keyword', 64)->nullable(false)->default('')->comment('关键词');
            $table->char('desc', 255)->nullable(false)->default('')->comment('描述');
            $table->integer('order')->nullable(false)->default(0)->comment('排序');
            $table->integer('status')->nullable(false)->default(0)->comment('状态');
            $table->timestamps();
        });
        Schema::table('articles_category',function (Blueprint $table){
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_category');
    }
}
