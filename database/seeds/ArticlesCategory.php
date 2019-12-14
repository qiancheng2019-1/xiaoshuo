<?php

use Illuminate\Database\Seeder;

class ArticlesCategory extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('articles_category')->insert([
            'name' => '玄幻',
            'title' => '玄幻标题',
            'keyword' => '玄幻关键字',
            'desc' => '玄幻描述',
            'order' => 1,
            'status' => 1,
        ]);
        DB::table('articles_category')->insert([
            'name' => '修真',
            'title' => '修真标题',
            'keyword' => '修真关键字',
            'desc' => '修真描述',
            'order' => 2,
            'status' => 1,
        ]);
        DB::table('articles_category')->insert([
            'name' => '都市',
            'title' => '都市标题',
            'keyword' => '都市关键字',
            'desc' => '都市描述',
            'order' => 3,
            'status' => 1,
        ]);
        DB::table('articles_category')->insert([
            'name' => '历史',
            'title' => '历史标题',
            'keyword' => '历史关键字',
            'desc' => '历史描述',
            'order' => 4,
            'status' => 1,
        ]);
    }
}
