<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_views', function (Blueprint $table) {
            $table->unsignedBigInteger('articles_id');
            $table->integer('week_views')->nullable(false)->default(0)->comment('周点击');
            $table->integer('month_views')->nullable(false)->default(0)->comment('月点击');
            $table->integer('total_views')->nullable(false)->default(0)->comment('总点击');
            $table->smallInteger('week')->nullable(false)->default(0)->comment('最后统计周');
            $table->smallInteger('month')->nullable(false)->default(0)->comment('最后统计月');
            $table->timestamps();
        });

        Schema::table('articles_views', function (Blueprint $table) {
            $table->index('week_views');
            $table->index('month_views');
            $table->index('total_views');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_views');
    }
}
