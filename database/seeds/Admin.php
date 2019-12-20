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
        DB::table('admin_users')->insert([
            'nickname' => 'Admin',
            'username' => 'admin',
            'password' => bcrypt('qweqwe'),
            'api_token' => Hash::make(Str::random(60)),
            'group_id' => 1,
        ]);

        DB::table('admin_users')->insert([
            'nickname' => 'AdminTest',
            'username' => 'test',
            'password' => bcrypt('qweqwe'),
            'api_token' => Hash::make(Str::random(60)),
            'group_id' => 1,
        ]);
    }
}
