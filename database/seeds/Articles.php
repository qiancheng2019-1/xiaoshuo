<?php

use Illuminate\Database\Seeder;

class Articles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = DB::table('articles')->insertGetId([
            'title' => '庆余年',
            'author' => '猫腻',
            'url' => 'https://www.35kushu.com/35zwhtml/96/96854/',
            'info' => '江湖在上，其人在下，江湖在左，其人在右。天为盘，星为子，地为盘，人为子。陆大少爷一脚踏进了名为江湖的地方，于是，一不小心捅出个窟窿！江湖在上，其人在下，江湖在左，其人在右。天为盘，星为子，地为盘，人为子。陆大少爷一脚踏进了名为江湖的地方，于是，一不小心捅出个窟窿！江湖在上，其人在下，江湖在左，其人在右。天为盘，星为子，地为盘，人为子。陆大少爷一脚踏进了名为江湖的地方，于是，一不小心捅出个窟窿！江湖在上，其人在下，江湖在左，其人在右。天为盘，星为子，地为盘，人为子。陆大少爷一脚踏进了名为江湖的地方，于是，一不小心捅出个窟窿！江湖在上，其人在下，江湖在左，其人在右。天为盘，星为子，地为盘，人为子。陆大少爷一脚踏进了名为江湖的地方，于是，一不小心捅出个窟窿！江湖在上，其人在下，江湖在左，其人在右。天为盘，星为子，地为盘，人为子。陆大少爷一脚踏进了名为江湖的地方，于是，一不小心捅出个窟窿！',
            'thumb' => '/storage/YBWz5yBXBSD4ZmkaWAqfodsNokIa7zdT9MSQBX6W.jpeg',
            'category_id' => 1,
            'category' => '玄幻',
            'last_chapter' => ' 第432章：收服蝎子精',
            'last_chapter_id' => 434,
            'full' => 0,
            'status' => 1,
        ]);

        DB::table('articles_views')->insert([
            'articles_id' => $id,
        ]);
    }
}
