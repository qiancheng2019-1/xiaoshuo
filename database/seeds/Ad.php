<?php

use Illuminate\Database\Seeder;

class Ad extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('ad')->insert(['key' => 'global_footer','name'=>'全局底部']);
        DB::table('ad')->insert(['key' => 'index_1','name'=>'首页广告1']);
        DB::table('ad')->insert(['key' => 'index_2','name'=>'首页广告2']);
        DB::table('ad')->insert(['key' => 'index_3','name'=>'首页广告3']);
        DB::table('ad')->insert(['key' => 'list_1','name'=>'列表广告1']);
        DB::table('ad')->insert(['key' => 'list_2','name'=>'列表广告2']);
        DB::table('ad')->insert(['key' => 'list_3','name'=>'列表广告3']);
        DB::table('ad')->insert(['key' => 'view_1','name'=>'信息页广告1']);
        DB::table('ad')->insert(['key' => 'view_2','name'=>'信息页广告2']);
        DB::table('ad')->insert(['key' => 'view_3','name'=>'信息页广告3']);
        DB::table('ad')->insert(['key' => 'chapter_1','name'=>'章节页广告1']);
        DB::table('ad')->insert(['key' => 'chapter_2','name'=>'章节页广告2']);
        DB::table('ad')->insert(['key' => 'chapter_3','name'=>'章节页广告3']);
    }
}
