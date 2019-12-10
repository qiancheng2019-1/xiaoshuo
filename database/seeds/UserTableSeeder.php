<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'nickname' => 'Admin',
            'username' => 'admin',
            'password' => bcrypt('qweqwe'),
            'api_token' => hash('sha256', Str::random(16)),
            'group_id' => 1,
        ]);
    }
}
