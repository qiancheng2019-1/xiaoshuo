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
        $time = ['created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];
        //
        DB::table('ad')->insert(['key' => 'global_header','name'=>'全局头部']+$time);
        DB::table('ad')->insert(['key' => 'global_footer','name'=>'全局底部']+$time);
        DB::table('ad')->insert(['key' => 'index_1','name'=>'首页广告1']+$time);
        DB::table('ad')->insert(['key' => 'index_2','name'=>'首页广告2']+$time);
        DB::table('ad')->insert(['key' => 'index_3','name'=>'首页广告3']+$time);
        DB::table('ad')->insert(['key' => 'index_4','name'=>'首页广告4']+$time);
        DB::table('ad')->insert(['key' => 'list_1','name'=>'列表广告1']+$time);
        DB::table('ad')->insert(['key' => 'list_2','name'=>'列表广告2']+$time);
        DB::table('ad')->insert(['key' => 'list_3','name'=>'列表广告3']+$time);
        DB::table('ad')->insert(['key' => 'list_4','name'=>'列表广告4']+$time);
        DB::table('ad')->insert(['key' => 'view_1','name'=>'信息页广告1']+$time);
        DB::table('ad')->insert(['key' => 'view_2','name'=>'信息页广告2']+$time);
        DB::table('ad')->insert(['key' => 'view_3','name'=>'信息页广告3']+$time);
        DB::table('ad')->insert(['key' => 'view_4','name'=>'信息页广告4']+$time);
        DB::table('ad')->insert(['key' => 'chapter_1','name'=>'章节页广告1']+$time);
        DB::table('ad')->insert(['key' => 'chapter_2','name'=>'章节页广告2']+$time);
        DB::table('ad')->insert(['key' => 'chapter_3','name'=>'章节页广告3']+$time);
        DB::table('ad')->insert(['key' => 'chapter_4','name'=>'章节页广告4']+$time);
    }
}
