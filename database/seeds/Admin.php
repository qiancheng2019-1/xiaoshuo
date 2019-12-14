<?php

use Illuminate\Database\Seeder;

class Admin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        //
//
        DB::table('users')->insert([
            'nickname' => 'Admin',
            'username' => 'admin',
            'password' => bcrypt('qweqwe'),
            'api_token' => hash('sha256', Str::random(60)),
            'group_id' => 1,
        ]);

        DB::table('users')->insert([
            'nickname' => 'AdminTest',
            'username' => 'test',
            'password' => bcrypt('qweqwe'),
            'api_token' => hash('sha256', Str::random(60)),
            'group_id' => 1,
        ]);
    }
}
