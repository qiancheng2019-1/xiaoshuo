<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('nickname', 128)->nullable(false)->default('');
            $table->char('username', 64)->nullable(false)->default('')->unique();
            $table->char('password', 64)->nullable(false)->default('');
            $table->string('avatar', 128)->nullable(false)->default('')->comment('头像');
            $table->char('api_token', 64)->nullable(false)->default('')->unique();
            $table->bigInteger('group_id')->nullable(false)->default(0);
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
        Schema::dropIfExists('users');
    }
}
